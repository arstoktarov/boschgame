<?php

namespace App\Models;

use App\Casts\Image;
use App\Traits\HashesPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Http\Controllers\v1\Rest\GameController;

/**
 * @property mixed scores
 * @method static findOrFail($user_id)
 * @method static orderBy(string $string)
 */

class User extends Authenticatable implements JWTSubject
{
    use Notifiable, HashesPassword;

    const IMAGE_PATH = 'users';

    const TOP_LIST_USERS_COUNT = 20;

    const RATING_COEFFICIENT_MAX = 30;
    const RATING_COEFFICIENT_MIN = 10;

    const FIRST_INTERVAL_MIN_POINTS = 1000;
    const FIRST_INTERVAL_MAX_COEFFICIENT = 30;

    const FIRST_INTERVAL_MAX_POINTS = 3000;
    const FIRST_INTERVAL_MIN_COEFFICIENT = 20;

    const SECOND_INTERVAL_MIN_POINTS = 3000;
    const SECOND_INTERVAL_MAX_COEFFICIENT = 20;

    const SECOND_INTERVAL_MAX_POINTS = 6000;
    const SECOND_INTERVAL_MIN_COEFFICIENT = 10;

    const FIRST_INTERVAL_STEP_VALUE = 200;
    const SECOND_INTERVAL_STEP_VALUE = 300;

    const STARTER_POINTS = 1000;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'phone', 'password', 'image',
        'login', 'workplace', 'organization', 'country_id', 'city_id',
        'device_token', 'device_type'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'phone_verified_at',
        'created_at', 'updated_at', 'access_token',
        'workplace', 'organization', 'country_id',
        'city_id', 'phone'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'image' => Image::class,
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected $appends = [
        'in_blacklist', 'in_friends', 'fullname', 'rating_coefficient'
    ];

    protected static function booted()
    {
//        static::addGlobalScope('withoutGuest', function(Builder $builder) {
//            $builder->where('users.id', '!=', Game::DEFAULT_PLAYER_ID);
//        });
    }

    public function scopeWithoutDefaultPlayer($query) {
        $query->where('users.id', '!=', Game::DEFAULT_PLAYER_ID);
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    #region Relationships

    public function friends() {
        return $this->belongsToMany(User::class, 'user_friends', 'user_id', 'friend_id');
    }

    public function friendsInterTable() {
        return $this->hasMany(UserFriend::class, 'user_id');
    }

    public function blacklistedUsers() {
        return $this->belongsToMany(User::class, 'user_blacklists', 'user_id', 'blacklisted_id');
    }

    public function blacklistsInterTable() {
        return $this->hasMany(UserBlacklist::class, 'user_id');
    }

    public function games() {
        return $this->belongsToMany(Game::class, 'game_users');
    }

    public function activeGames() {
        return $this->belongsToMany(Game::class, 'game_users')
            ->whereIn('status', [Game::STATUS_STARTED, Game::STATUS_WAITING_FOR_ENEMY]);
    }

    #endregion


    #region Accessors

    public function getInBlacklistAttribute() {
        if (!auth()->user()) return false;
        return auth()->user()->blacklistsInterTable()->where('blacklisted_id', $this->id)->exists();
    }

    public function getInFriendsAttribute() {
        if (!auth()->user()) return false;
        return auth()->user()->friendsInterTable()->where('friend_id', $this->id)->exists();
    }

    public function getFullNameAttribute() {
        return "$this->first_name $this->last_name";
    }

    public function getRatingCoefficientAttribute() {
        return $this->calculateRatingCoefficient();
    }

    #endregion

    public function rounds() {
        return $this->games()
            ->join('rounds', 'rounds.game_id', 'games.id');
    }

    public function getPointsAvg() {
        $avg_points = DB::query()
            ->fromSub(function($query) {
                $query->from('round_user_answers')
                    ->select('games.id', DB::raw('COUNT(round_user_answers.id) AS points_count'))
                    ->join('answers', 'round_user_answers.answer_id', '=', 'answers.id')
                    ->join('rounds', 'round_user_answers.round_id', '=', 'rounds.id')
                    ->join('games', 'rounds.game_id', '=', 'games.id')
                    ->where('round_user_answers.user_id', '=', $this->id)
                    ->where('is_correct', 1)
                    ->groupBy('games.id');
            }, 't2')->avg('t2.points_count');

        return round($avg_points);
    }

    public function getRowNumber($orderBy = 'scores') {
        $table = $this->getTable();
        $id = $this->id;
        $data = DB::select(
            DB::raw(
                "SELECT t.rank
                    FROM
                        (
                            SELECT u.id, @curRank := @curRank + 1 AS rank
                            FROM $table u, (SELECT @curRank := 0) r
                            ORDER BY u.$orderBy DESC
                        ) AS t
                    WHERE t.id = $id LIMIT 1"
            )
        );
        return $data[0]->rank ?? 0;
    }

    /**
     * Считает коэффициент очков рейтинга пользователя.
     *
     * Это коэффициент, который меняется в зависимости от рейтинга.
     * Чем выше рейтинг, тем меньше данный коэффициент.
     * Коэффициент будет колебаться от 30 до 10.
     * До 1000 очков коэффициент составляет 30, далее от 1000 до 3000 каждый шаг составит 200 очков,
     * и уменьшение коэффициента на 1 (например, 1000 очков – 30, 1200 очков – 29 и т.д.).
     * От 3000 очков до 6000 очков каждый шаг увеличивается до 300 очков, коэффициент,
     * соответственно, уменьшается на 1 (например, 3000 очков – 20, 3300 очков - 19).
     *
     * @return float|int
     */
    public function calculateRatingCoefficient() {

        if ($this->scores < self::FIRST_INTERVAL_MIN_POINTS) {
            return self::RATING_COEFFICIENT_MAX;
        }

        if ($this->scores > self::SECOND_INTERVAL_MAX_POINTS) {
            return self::RATING_COEFFICIENT_MIN;
        }

        $coefficient = self::RATING_COEFFICIENT_MAX;

        if ($this->inFirstInterval()) {
            $coefficient = self::FIRST_INTERVAL_MAX_COEFFICIENT - floor( ($this->scores - self::FIRST_INTERVAL_MIN_POINTS) / self::FIRST_INTERVAL_STEP_VALUE );
        }
        elseif ($this->inSecondInterval()) {
            $coefficient = self::SECOND_INTERVAL_MAX_COEFFICIENT - floor( ($this->scores - self::SECOND_INTERVAL_MIN_POINTS) / self::SECOND_INTERVAL_STEP_VALUE );
        }

        if ($coefficient < self::RATING_COEFFICIENT_MIN) $coefficient = self::RATING_COEFFICIENT_MIN;
        if ($coefficient > self::RATING_COEFFICIENT_MAX) $coefficient = self::RATING_COEFFICIENT_MAX;

        return $coefficient;
    }

    /**
     * Считает мат ожидание(вероятность победы) пользователя на основе очков противника.
     * Если у противника больше очков, вероятность победить меньше, и наоборот соответсвенно.
     *
     * Формула: E(A) = 1 / (1 + 10^(R(A) - R(B) / 400))
     * Где
     * E(A) - Мат ожидание пользователя A
     * R(A) - Очки рейтинга(scores) пользователя A
     * R(A) - Очки рейтинга(scores) пользователя B
     *
     *
     * @param $enemyScores - очки пользователя B
     * @return float|int
     */
    public function calculateMathExpectation(int $enemyScores) {
        return 1 / (1 + pow(10, ($enemyScores - $this->scores) / 400));
    }

    /**
     * Считает новые очки рейтинга пользователя на основе очков противника и
     * коэффициенте результата игрока в игре(1-победа, 0-поражение, 0.5-ничья)
     *
     * Формула: R'(A) = R(A) + k(S(A) - E(A))
     * Где
     * R'(A) - новые очки пользователя
     * R(A) - старые очки пользователя
     * k - коэффициент очков рейтинга пользователя
     * S(A) - Коэффициент результата пользователя в игре (1-победа, 0-поражение, 0.5-ничья)
     * E(A) - Мат. ожидание пользователя (вероятность игрока победить в игре) (объяснено в PHPDoc функции calculateMathExpectation)
     *
     * @param $enemyScores - очки противника
     * @param $gameCoefficient - коэффициент игры (1-победа, 0-поражение, 0.5-ничья)
     * @return float|int|mixed
     */
    public function calculateNewScores(int $enemyScores, $gameCoefficient) {

        $winProbability = $this->calculateMathExpectation($enemyScores);
        $userRatingCoefficient = $this->calculateRatingCoefficient();

        return $this->scores + ($userRatingCoefficient * ($gameCoefficient - $winProbability));
    }

    public function newScore(int $enemyScores, $gameCoefficient) {
        $winProbability = $this->calculateMathExpectation($enemyScores);
        $userRatingCoefficient = $this->calculateRatingCoefficient();

        return $this->$userRatingCoefficient * ($gameCoefficient - $winProbability);

    }


    public function setNewScores($enemyScores, $gameCoefficient) {
        $this->scores = $this->calculateNewScores($enemyScores, $gameCoefficient);
        $this->save();
    }


    /**
     * Первый интервал описывает игрока с очками рейтинга от 1000 до 3000
     *
     * @return bool
     */
    public function inFirstInterval() {
        return ($this->scores > self::FIRST_INTERVAL_MIN_POINTS && $this->scores <= self::FIRST_INTERVAL_MAX_POINTS);
    }

    /**
     * Первый интервал описывает игрока с очками рейтинга от 3000 до 6000
     *
     * @return bool
     */
    public function inSecondInterval() {
        return ($this->scores > self::SECOND_INTERVAL_MIN_POINTS && $this->scores <= self::SECOND_INTERVAL_MAX_POINTS);
    }

}

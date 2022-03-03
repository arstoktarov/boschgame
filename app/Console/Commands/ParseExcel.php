<?php

namespace App\Console\Commands;

use App\Models\Answer;
use App\Models\Category;
use App\Models\Question;
use Faker\Generator as Faker;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use SimpleXLSX;
use function Couchbase\defaultDecoder;

class ParseExcel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'excel:parse';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @param Faker $faker
     * @return void
     */
    public function handle(Faker $faker)
    {
        $file = Storage::get('7.xlsx');
        $data = SimpleXLSX::parseData($file);


          if ( $data->success() ) {
             print_r( $data->rows() );
           } else {
             echo 'xlsx error: '.$data->error();
          }

        $category = Category::where('id' , 18)->first();
//        dd('ok');
        //85606
        //17336

        //1-2-3-4-5-6-7


           foreach ($data->sheetNames() as $sheetIndex => $sheetName) {
           // $category->title = $sheetName;
           // $category->image = $faker->imageUrl();
           // $category->save();
            foreach ($data->rows($sheetIndex ) as $row) {
                $image = array_shift($row);
                $questionRow = array_shift($row);
                $question = new Question();
                $question->title = $questionRow;
                $question->category_id = $category->id;
                $question->image = $image;
                $question->save();

                $correctAnswerRow = array_shift($row);
                $answer = new Answer();
                $answer->title = $correctAnswerRow;
                $answer->is_correct = 1;
                $answer->question_id = $question->id;
                $answer->save();

                foreach ($row as $index => $column) {

                    if ($index < 3) {
                        $answer = new Answer();
                        $answer->title = $column;
                        $answer->is_correct = 0;
                        $answer->question_id = $question->id;
                        $answer->save();
                    }
                }
            }
        }
           print('loaded');
       // $this->info(json_encode($data->rows()));
    }

    //dd(mb_convert_case($data->rows(1)[2][1], MB_CASE_TITLE, 'UTF-8'));
    // dd($data->rows(1));
    // dd('oooh, stop it you maaan');

}

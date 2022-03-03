<?php

namespace App\Http\Controllers;

use App\Http\Resources\AuthorizedUserResource;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    const PAGINATION_COUNT = 20;

    public static function errorResponse(string $message, int $statusCode) {
        return response()->json([
            'success' => false,
            'statusCode' => $statusCode,
            'message' => $message,
            'data' => null,
        ], $statusCode);
    }

    public static function Response($statusCode, $data, $message = null) {
        return response()->json([
            'success' => boolval($statusCode == 200),
            'statusCode' => $statusCode,
            'message' => $message ?? self::getDefaultMessage($statusCode),
            'data' => $data,
        ], $statusCode);
    }

    public static function getDefaultMessage(int $statusCode) {
        return trans("httpmessages.$statusCode");
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return self::Response(200, [
            'access_token' => $token,
            'user' => new AuthorizedUserResource(auth()->user()),
            'token_type' => 'bearer',
//            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    public function paginatedToResourceCollection($paginated, $resource) {
        $collection = $paginated->getCollection();
        $collection = $resource::collection($collection)->collection;
        $paginated->setCollection($collection);
        return $paginated;
    }
}

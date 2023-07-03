<?php
/**
 * Created by PhpStorm
 * Filename: APIResponse.php
 * User: quanph
 * Date: 30/06/2023
 * Time: 09:39
 */

namespace App\Contracts\APIResponse;

use App\Enums\APIResponseType;

class APIResponse
{
    public static function response(
        ?array $data = null,
        string $type = APIResponseType::SUCCESS,
        ?string $message = null,
        ?string $error = null,
        ?array $meta = null,
        ?array $pagination = null
    ): array {
        return [
            'data'       => $data,
            'type'       => $type,
            'message'    => $message,
            'error'      => $error,
            'meta'       => $meta,
            'pagination' => $pagination,
        ];
    }

    public static function success(
        ?array $data = null,
        ?string $message = null,
        ?array $meta = null,
        ?array $pagination = null
    ): array {
        return self::response($data, APIResponseType::SUCCESS, $message, null, $meta, $pagination);
    }

    public static function error(
        ?string $error = null,
        ?string $message = null,
        ?array $meta = null,
        ?array $pagination = null
    ): array {
        return self::response(null, APIResponseType::ERROR, $message, $error, $meta, $pagination);
    }
}

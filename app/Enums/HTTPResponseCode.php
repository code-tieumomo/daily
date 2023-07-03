<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OK()
 * @method static static CREATED()
 * @method static static ACCEPTED()
 * @method static static NO_CONTENT()
 * @method static static MOVED_PERMANENTLY()
 * @method static static FOUND()
 * @method static static SEE_OTHER()
 * @method static static BAD_REQUEST()
 * @method static static UNAUTHORIZED()
 * @method static static FORBIDDEN()
 * @method static static NOT_FOUND()
 * @method static static METHOD_NOT_ALLOWED()
 * @method static static CONFLICT()
 * @method static static INTERNAL_SERVER_ERROR()
 * @method static static NOT_IMPLEMENTED()
 * @method static static SERVICE_UNAVAILABLE()
 */
final class HTTPResponseCode extends Enum
{
    const OK = 200;
    const CREATED = 201;
    const ACCEPTED = 202;
    const NO_CONTENT = 204;
    const MOVED_PERMANENTLY = 301;
    const FOUND = 302;
    const SEE_OTHER = 303;
    const BAD_REQUEST = 400;
    const UNAUTHORIZED = 401;
    const FORBIDDEN = 403;
    const NOT_FOUND = 404;
    const METHOD_NOT_ALLOWED = 405;
    const CONFLICT = 409;
    const INTERNAL_SERVER_ERROR = 500;
    const NOT_IMPLEMENTED = 501;
    const SERVICE_UNAVAILABLE = 503;
}

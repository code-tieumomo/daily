<?php
/**
 * Created by PhpStorm
 * Filename: lms.php
 * User: quanph
 * Date: 28/06/2023
 * Time: 01:04
 */

return [
    // Số lần thử đăng nhập tối đa
    'max_login_attempts' => 3,

    // Khoảng thời gian chênh lệnh (tính bằng giây) so với giờ học (Ví dụ: 5 phút trước giờ học, 5 phút sau giờ học)
    'in_class_diff'      => 5 * 60,

    // Các tiêu chí tìm kiếm lớp học
    'classes_criteria'   => [
        "search"               => "",
        "centres"              => [],
        "courses"              => [],
        "courseLines"          => [],
        "startDate"            => [null, null],
        "endDate"              => [null, null],
        "pageIndex"            => 0,
        "itemsPerPage"         => 20,
        "orderBy"              => "createdAt_desc",
        "type"                 => "OFFSET",
        "teacherSlot"          => [],
        "passedSessionIndex"   => null,
        "unpassedSessionIndex" => null,
        "haveSlotIn"           => ["from" => null, "to" => null],
        "comments"             => ["criteria" => []],
    ],
];

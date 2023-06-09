<?php
/**
 * Created by PhpStorm
 * Filename: ClassService.php
 * User: quanph
 * Date: 27/06/2023
 * Time: 08:55
 */

namespace App\Http\Services;

use Illuminate\Support\Facades\Http;

/**
 * Class APIService: This class is used to make API calls to the MindXLMS API.
 */
class APIService
{
    /**
     * Login to the MindXLMS API with the given email and password.
     *
     * @param  string  $email  Email
     * @param  string  $password  Password
     */
    static function login($email, $password)
    {
        $loginEndpoint = 'https://www.googleapis.com/identitytoolkit/v3/relyingparty/verifyPassword?key=AIzaSyAh2Au-mk5ci-hN83RUBqj1fsAmCMdvJx4';

        $response = Http::post($loginEndpoint, [
            'email'             => $email,
            'password'          => $password,
            'returnSecureToken' => true,
        ]);

        return $response->object();
    }

    /**
     * Get the account info for the given token.
     *
     * @param  string  $token  Access token
     */
    static function getAccountInfo($token)
    {
        $getAccountInfoEndpoint = 'https://www.googleapis.com/identitytoolkit/v3/relyingparty/getAccountInfo?key=AIzaSyAh2Au-mk5ci-hN83RUBqj1fsAmCMdvJx4';

        $response = Http::post($getAccountInfoEndpoint, [
            'idToken' => $token,
        ]);

        return $response->object();
    }

    /**
     * Find the info for the given role and id.
     *
     * @param  string  $token  Access token
     * @param  string  $id  Account id
     */
    static function findInfoInRoleById($token, $id)
    {
        $findInfoInRoleByIdEndpoint = 'https://lms-api.mindx.vn/';

        $response = Http::withHeaders(
            [
                'Authorization' => $token,
            ]
        )->withBody(
            '
                {
                    "operationName":"FindInfoInRoleById",
                    "variables":{
                        "payload":{
                            "id":"' . $id . '"
                        }
                    },
                    "query":"mutation FindInfoInRoleById($payload: FindInfoInRoleByIdCommand!) {\n  users {\n    findInfoInRoleById(payload: $payload) {\n      info\n      role\n    }\n  }\n}\n"
                }
            ',
            'application/json'
        )->post($findInfoInRoleByIdEndpoint);

        return $response->object();
    }

    static function getClasses($token, $teacherId, $page = 1, $number = 20, $criteria = [])
    {
        $getClassesEndpoint = "https://lms-api.mindx.vn/";
        if (empty($criteria)) {
            $criteria = config('lms.classes_criteria');
        }

        $criteria['pageIndex']    = $page - 1;
        $criteria['itemsPerPage'] = $number;
        $criteria['teacherId']    = $teacherId;

        $response = Http::withHeaders(
            [
                'Authorization' => $token,
            ]
        )->withBody(
            '
                {
                    "operationName":"GetClasses",
                    "variables":' . json_encode($criteria) . ',
                    "query":"query GetClasses($search: String, $centre: String, $centres: [String], $courses: [String], $courseLines: [String], $startDateFrom: Date, $startDateTo: Date, $endDateFrom: Date, $endDateTo: Date, $haveSlotFrom: Date, $haveSlotTo: Date, $statusNotEquals: String, $attendanceCheckedExists: Boolean, $status: String, $statusIn: [String], $attendanceStatus: [String], $studentAttendanceStatus: [String], $teacherAttendanceStatus: [String], $pageIndex: Int!, $itemsPerPage: Int!, $orderBy: String, $teacherId: String, $teacherSlot: [String], $passedSessionIndex: Int, $unpassedSessionIndex: Int, $haveSlotIn: HaveSlotIn, $comments: ClassCommentQuery) {\n  classes(payload: {filter_textSearch: $search, centre_equals: $centre, centre_in: $centres, teacher_equals: $teacherId, teacherSlots: $teacherSlot, course_in: $courses, courseLine_in: $courseLines, startDate_gt: $startDateFrom, startDate_lt: $startDateTo, endDate_gt: $endDateFrom, endDate_lt: $endDateTo, haveSlot_from: $haveSlotFrom, haveSlot_to: $haveSlotTo, status_ne: $statusNotEquals, status_in: $statusIn, status_equals: $status, attendanceStatus_in: $attendanceStatus, studentAttendanceStatus_in: $studentAttendanceStatus, teacherAttendanceStatus_in: $teacherAttendanceStatus, attendanceChecked_exists: $attendanceCheckedExists, haveSlot_in: $haveSlotIn, passedSessionIndex: $passedSessionIndex, unpassedSessionIndex: $unpassedSessionIndex, pageIndex: $pageIndex, itemsPerPage: $itemsPerPage, orderBy: $orderBy, comments: $comments}) {\n    data {\n      id\n      name\n      course {\n        id\n        name\n        shortName\n      }\n      startDate\n      endDate\n      status\n      centre {\n        id\n        name\n        shortName\n      }\n      openingRoomNo\n      numberOfSessions\n      numberOfSessionsStatus\n      sessionHour\n      totalHour\n      slots {\n        _id\n        date\n        startTime\n        endTime\n        sessionHour\n        summary\n        homework\n        teachers {\n          _id\n          teacher {\n            id\n            username\n            fullName\n            email\n            phoneNumber\n            user\n            imageUrl\n          }\n          role {\n            id\n            name\n            shortName\n          }\n          isActive\n        }\n        teacherAttendance {\n          _id\n          teacher {\n            id\n            username\n            fullName\n            email\n            phoneNumber\n            user\n            imageUrl\n          }\n          status\n          note\n          createdBy\n          createdAt\n          lastModifiedBy\n          lastModifiedAt\n        }\n        studentAttendance {\n          _id\n          student {\n            id\n            fullName\n            phoneNumber\n            email\n            gender\n            imageUrl\n          }\n          status\n          comment\n          sendCommentStatus\n        }\n      }\n      students {\n        _id\n        student {\n          id\n          customer {\n            fullName\n            phoneNumber\n            email\n            facebook\n            zalo\n          }\n        }\n        note\n        activeInClass\n        createdBy\n        createdAt\n      }\n      teachers {\n        _id\n        teacher {\n          id\n          username\n          fullName\n          imageUrl\n          email\n          phoneNumber\n        }\n        role {\n          id\n          name\n          shortName\n          description\n          isActive\n        }\n        isActive\n      }\n      operator {\n        id\n        username\n        firstName\n        middleName\n        lastName\n      }\n      hasSchedule\n      createdBy\n      createdAt\n      lastModifiedBy\n      lastModifiedAt\n    }\n    pagination {\n      type\n      total\n    }\n  }\n}\n"
                }
            ',
            'application/json'
        )->retry(3, 1000)->post($getClassesEndpoint);

        return $response->object();
    }

    static function getClassById($token, $classId)
    {
        $getClassesEndpoint = "https://lms-api.mindx.vn/";

        $response = Http::withHeaders(
            [
                'Authorization' => $token,
            ]
        )->withBody(
            '
                {
                    "operationName":"GetClassById",
                    "variables":{
                        "id":"' . $classId . '"
                    },
                    "query":"query GetClassById($id: ID!) {\n  classesById(id: $id) {\n    id\n    name\n    course {\n      id\n      name\n      shortName\n      isActive\n      numberOfSession\n      sessionHour\n      description\n      minStudents\n      maxEnrollSession\n      maxStudents\n      optimalStudents\n      oneSessionSettings {\n        _id\n        classRole {\n          id\n          name\n          shortName\n          description\n          isActive\n          createdAt\n          createdBy\n          lastModifiedAt\n          lastModifiedBy\n        }\n        quantity\n      }\n      sessionSettings {\n        _id\n        sessionNumber\n        settings {\n          _id\n          classRole {\n            id\n            name\n            shortName\n            description\n            isActive\n            createdAt\n            createdBy\n            lastModifiedAt\n            lastModifiedBy\n          }\n          quantity\n        }\n      }\n    }\n    startDate\n    endDate\n    status\n    centre {\n      id\n      name\n      shortName\n      hotline\n      email\n      address\n      isActive\n    }\n    histories {\n      action\n      changes {\n        op\n        path\n        from\n        to\n      }\n      createdBy {\n        displayName\n      }\n      createdAt\n    }\n    openingRoomNo\n    numberOfSessions\n    numberOfSessionsStatus\n    sessionHour\n    totalHour\n    slots {\n      _id\n      date\n      startTime\n      endTime\n      sessionHour\n      teachers {\n        _id\n        teacher {\n          id\n          username\n          code\n          fullName\n          email\n          phoneNumber\n          user\n          imageUrl\n        }\n        role {\n          id\n          name\n          shortName\n        }\n        isActive\n      }\n      teacherAttendance {\n        _id\n        teacher {\n          id\n          username\n          fullName\n          email\n          phoneNumber\n          user\n          imageUrl\n        }\n        status\n        note\n        createdBy\n        createdAt\n        lastModifiedBy\n        lastModifiedAt\n      }\n      studentAttendance {\n        _id\n        student {\n          id\n          fullName\n          phoneNumber\n          email\n          gender\n          imageUrl\n          customer {\n            email\n          }\n        }\n        comment\n        sendCommentStatus\n        status\n        commentByAreas {\n          grade\n          content\n          commentAreaId\n        }\n        createdBy\n        createdAt\n        lastModifiedBy\n        lastModifiedAt\n      }\n      summary\n      homework\n      createdAt\n      createdBy\n      lastModifiedAt\n      lastModifiedBy\n    }\n    scheduleSettings {\n      _id\n      date\n      startTime\n      endTime\n      repeated\n    }\n    students {\n      _id\n      student {\n        id\n        fullName\n        phoneNumber\n        email\n        gender\n        dob\n        address\n        imageUrl\n        facebook\n        zalo\n        school\n        customer {\n          fullName\n          phoneNumber\n          email\n          facebook\n          zalo\n        }\n      }\n      note\n      activeInClass\n      completed\n      createdBy\n      createdAt\n    }\n    teachers {\n      _id\n      teacher {\n        id\n        username\n        fullName\n        imageUrl\n        email\n        phoneNumber\n      }\n      role {\n        id\n        name\n        shortName\n        description\n        isActive\n      }\n      isActive\n    }\n    operator {\n      id\n      email\n      firstName\n      middleName\n      lastName\n      displayName\n      username\n    }\n    contactTeacher {\n      id\n      email\n      phoneNumber\n      fullName\n      code\n      username\n    }\n    hasSchedule\n    links {\n      _id\n      name\n      url\n    }\n    createdBy\n    createdAt\n    lastModifiedBy\n    lastModifiedAt\n  }\n}\n"
                }
            ',
            'application/json'
        )->post($getClassesEndpoint);

        return $response->object();
    }

    // static function setSummary($accessToken, $classId, $slotId, $summary)
    // {
    //     $curl = curl_init();

    //     curl_setopt_array($curl, [
    //         CURLOPT_URL => 'https://lms-api.mindx.vn/',
    //         CURLOPT_SSL_VERIFYPEER => 0,
    //         CURLOPT_RETURNTRANSFER => true,
    //         CURLOPT_ENCODING => '',
    //         CURLOPT_MAXREDIRS => 10,
    //         CURLOPT_TIMEOUT => 0,
    //         CURLOPT_FOLLOWLOCATION => true,
    //         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //         CURLOPT_CUSTOMREQUEST => 'POST',
    //         CURLOPT_POSTFIELDS => '{
    //             "operationName": "UpdateSlotComment",
    //             "variables": {
    //                 "payload": {
    //                     "slotId": "' . $slotId . '",
    //                     "summary": "<p>' . $summary . '</p",
    //                     "classId": "' . $classId . '"
    //                 }
    //             },
    //             "query": "mutation UpdateSlotComment($payload: UpdateSlotCommentCommand!) {\n  classes {\n    updateSlotComment(payload: $payload) {\n      id\n      name\n      course {\n        id\n        name\n        shortName\n        isActive\n        numberOfSession\n        sessionHour\n        description\n        minStudents\n        maxEnrollSession\n        maxStudents\n        optimalStudents\n        oneSessionSettings {\n          _id\n          classRole {\n            id\n            name\n            shortName\n            description\n            isActive\n            createdAt\n            createdBy\n            lastModifiedAt\n            lastModifiedBy\n          }\n          quantity\n        }\n        sessionSettings {\n          _id\n          sessionNumber\n          settings {\n            _id\n            classRole {\n              id\n              name\n              shortName\n              description\n              isActive\n              createdAt\n              createdBy\n              lastModifiedAt\n              lastModifiedBy\n            }\n            quantity\n          }\n        }\n      }\n      startDate\n      endDate\n      status\n      centre {\n        id\n        name\n        shortName\n        hotline\n        email\n        address\n        isActive\n      }\n      histories {\n        action\n        changes {\n          op\n          path\n          from\n          to\n        }\n        createdBy {\n          displayName\n        }\n        createdAt\n      }\n      openingRoomNo\n      numberOfSessions\n      numberOfSessionsStatus\n      sessionHour\n      totalHour\n      slots {\n        _id\n        date\n        startTime\n        endTime\n        sessionHour\n        teachers {\n          _id\n          teacher {\n            id\n            username\n            code\n            fullName\n            email\n            phoneNumber\n            user\n            imageUrl\n          }\n          role {\n            id\n            name\n            shortName\n          }\n          isActive\n        }\n        teacherAttendance {\n          _id\n          teacher {\n            id\n            username\n            fullName\n            email\n            phoneNumber\n            user\n            imageUrl\n          }\n          status\n          note\n          createdBy\n          createdAt\n          lastModifiedBy\n          lastModifiedAt\n        }\n        studentAttendance {\n          _id\n          student {\n            id\n            fullName\n            phoneNumber\n            email\n            gender\n            imageUrl\n            customer {\n              email\n            }\n          }\n          comment\n          sendCommentStatus\n          status\n          commentByAreas {\n            grade\n            content\n            commentAreaId\n          }\n          createdBy\n          createdAt\n          lastModifiedBy\n          lastModifiedAt\n        }\n        summary\n        homework\n        createdAt\n        createdBy\n        lastModifiedAt\n        lastModifiedBy\n      }\n      scheduleSettings {\n        _id\n        date\n        startTime\n        endTime\n        repeated\n      }\n      students {\n        _id\n        student {\n          id\n          fullName\n          phoneNumber\n          email\n          gender\n          dob\n          address\n          imageUrl\n          facebook\n          zalo\n          school\n          customer {\n            fullName\n            phoneNumber\n            email\n            facebook\n            zalo\n          }\n        }\n        note\n        activeInClass\n        createdBy\n        createdAt\n      }\n      teachers {\n        _id\n        teacher {\n          id\n          username\n          fullName\n          imageUrl\n          email\n          phoneNumber\n        }\n        role {\n          id\n          name\n          shortName\n          description\n          isActive\n        }\n        isActive\n      }\n      operator {\n        id\n        email\n        firstName\n        middleName\n        lastName\n        displayName\n        username\n      }\n      contactTeacher {\n        id\n        email\n        phoneNumber\n        fullName\n        code\n        username\n      }\n      hasSchedule\n      links {\n        _id\n        name\n        url\n      }\n      createdBy\n      createdAt\n      lastModifiedBy\n      lastModifiedAt\n    }\n  }\n}\n"
    //         }',
    //         CURLOPT_HTTPHEADER => [
    //             'Authorization: ' . $accessToken,
    //             'Content-Type: application/json',
    //         ],
    //     ]);

    //     $response = curl_exec($curl);
    //     if (curl_errno($curl)) {
    //         $error_msg = curl_error($curl);
    //     }

    //     curl_close($curl);

    //     if (isset($error_msg)) {
    //         throw new Exception($error_msg);
    //     }

    //     return $response;
    // }

    static function setReview($token, $classId, $slotId, $studentId, $studentAttendanceId, $comment)
    {
        $setReviewEndpoint = 'https://lms-api.mindx.vn/';

        $response = Http::withHeaders(
            [
                'Authorization' => $token,
            ]
        )->withBody(
            '
                {
                    "operationName":"UpdateSlotComment",
                    "variables":{
                        "payload":{
                            "slotId":"' . $slotId . '",
                            "studentComment":{
                                "studentAttendanceId":"' . $studentAttendanceId . '",
                                "studentId":"' . $studentId . '",
                                "content":"' . $comment . '",
                                "byAreas":[

                                ]
                            },
                            "classId":"' . $classId . '"
                        }
                    },
                    "query":"mutation UpdateSlotComment($payload: UpdateSlotCommentCommand!) {\n  classes {\n    updateSlotComment(payload: $payload) {\n      id\n      name\n      slots {\n        _id\n        date\n        startTime\n        endTime\n        sessionHour\n        teachers {\n          _id\n          teacher {\n            id\n            username\n            code\n            fullName\n            email\n            phoneNumber\n            user\n            imageUrl\n          }\n          role {\n            id\n            name\n            shortName\n          }\n          isActive\n        }\n        teacherAttendance {\n          _id\n          teacher {\n            id\n            username\n            fullName\n            email\n            phoneNumber\n            user\n            imageUrl\n          }\n          status\n          note\n          createdBy\n          createdAt\n          lastModifiedBy\n          lastModifiedAt\n        }\n        studentAttendance {\n          _id\n          student {\n            id\n            fullName\n            phoneNumber\n            email\n            gender\n            imageUrl\n            customer {\n              email\n            }\n          }\n          comment\n          sendCommentStatus\n          status\n          commentByAreas {\n            grade\n            content\n            commentAreaId\n          }\n          createdBy\n          createdAt\n          lastModifiedBy\n          lastModifiedAt\n        }\n        summary\n        homework\n        createdAt\n        createdBy\n        lastModifiedAt\n        lastModifiedBy\n      }\n    }\n  }\n}\n"
                }
            ',
            'application/json'
        )->post($setReviewEndpoint);

        return $response->object();
    }

    // static function setReview($accessToken, $classId, $slotId, $studentAttendanceId, $content)
    // {
    //     $curl = curl_init();

    //     curl_setopt_array($curl, [
    //         CURLOPT_URL => 'https://lms-api.mindx.vn/',
    //         CURLOPT_SSL_VERIFYPEER => 0,
    //         CURLOPT_RETURNTRANSFER => true,
    //         CURLOPT_ENCODING => '',
    //         CURLOPT_MAXREDIRS => 10,
    //         CURLOPT_TIMEOUT => 0,
    //         CURLOPT_FOLLOWLOCATION => true,
    //         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //         CURLOPT_CUSTOMREQUEST => 'POST',
    //         CURLOPT_POSTFIELDS => '{
    //             "operationName": "UpdateSlotComment",
    //             "variables": {
    //                 "payload": {
    //                     "slotId": "' . $slotId . '",
    //                     "studentComment": {
    //                         "studentAttendanceId": "' . $studentAttendanceId . '",
    //                         "content": "- Thái độ học tập: Tập trung lắng nghe bài giảng, tự giác học tập, giáo viên hầu như không phải nhắc nhở con, hiệu quả buổi học cao<br>- Tư duy, kỹ năng giải quyết vấn đề : Phân tích vấn đề tốt, bắt đầu tự đưa ra giải pháp của riêng mình, giải pháp có tính logic, biết thử đi thử lại nhiều lần đến khi ra kết quả, con làm được đề bài khác mẫu khá tốt<br>- Thao tác chuột/ bàn phím: Tốc độ sử dụng chuột/bàn phím rất thành thạo, có thể sử dụng gõ phím bằng 2 tay không cần nhìn phím, con biết tận dụng tối ưu các phần mềm máy tính<br>- Cẩn thận, chỉn chu: Code của con chuẩn, cấu trúc dễ phối hợp, con có thói quen chỉn chu kiểm tra kỹ càng và sửa từng chi tiết<br>- Một số minh chứng về việc học của con trong lớp: ' . $content . '",
    //                         "byAreas": [
    //                             {
    //                                 "grade": 9,
    //                                 "content": "Tập trung lắng nghe bài giảng, tự giác học tập, giáo viên hầu như không phải nhắc nhở con, hiệu quả buổi học cao",
    //                                 "commentAreaId": "61690eced815c00060085161"
    //                             },
    //                             {
    //                                 "grade": 9,
    //                                 "content": "Phân tích vấn đề tốt, bắt đầu tự đưa ra giải pháp của riêng mình, giải pháp có tính logic, biết thử đi thử lại nhiều lần đến khi ra kết quả, con làm được đề bài khác mẫu khá tốt",
    //                                 "commentAreaId": "61690eced815c00060085177"
    //                             },
    //                             {
    //                                 "grade": 10,
    //                                 "content": "Tốc độ sử dụng chuột/bàn phím rất thành thạo, có thể sử dụng gõ phím bằng 2 tay không cần nhìn phím, con biết tận dụng tối ưu các phần mềm máy tính",
    //                                 "commentAreaId": "61690eced815c0006008516c"
    //                             },
    //                             {
    //                                 "grade": 10,
    //                                 "content": "Code của con chuẩn, cấu trúc dễ phối hợp, con có thói quen chỉn chu kiểm tra kỹ càng và sửa từng chi tiết",
    //                                 "commentAreaId": "61690eced815c000600851a3"
    //                             },
    //                             {
    //                                 "content": "' . $content . '",
    //                                 "commentAreaId": "61690eced815c000600851ae"
    //                             }
    //                         ]
    //                     },
    //                     "classId": "' . $classId . '"
    //                 }
    //             },
    //             "query": "mutation UpdateSlotComment($payload: UpdateSlotCommentCommand!) {\n  classes {\n    updateSlotComment(payload: $payload) {\n      id\n      name\n      course {\n        id\n        name\n        shortName\n        isActive\n        numberOfSession\n        sessionHour\n        description\n        minStudents\n        maxEnrollSession\n        maxStudents\n        optimalStudents\n        oneSessionSettings {\n          _id\n          classRole {\n            id\n            name\n            shortName\n            description\n            isActive\n            createdAt\n            createdBy\n            lastModifiedAt\n            lastModifiedBy\n          }\n          quantity\n        }\n        sessionSettings {\n          _id\n          sessionNumber\n          settings {\n            _id\n            classRole {\n              id\n              name\n              shortName\n              description\n              isActive\n              createdAt\n              createdBy\n              lastModifiedAt\n              lastModifiedBy\n            }\n            quantity\n          }\n        }\n      }\n      startDate\n      endDate\n      status\n      centre {\n        id\n        name\n        shortName\n        hotline\n        email\n        address\n        isActive\n      }\n      histories {\n        action\n        changes {\n          op\n          path\n          from\n          to\n        }\n        createdBy {\n          displayName\n        }\n        createdAt\n      }\n      openingRoomNo\n      numberOfSessions\n      numberOfSessionsStatus\n      sessionHour\n      totalHour\n      slots {\n        _id\n        date\n        startTime\n        endTime\n        sessionHour\n        teachers {\n          _id\n          teacher {\n            id\n            username\n            code\n            fullName\n            email\n            phoneNumber\n            user\n            imageUrl\n          }\n          role {\n            id\n            name\n            shortName\n          }\n          isActive\n        }\n        teacherAttendance {\n          _id\n          teacher {\n            id\n            username\n            fullName\n            email\n            phoneNumber\n            user\n            imageUrl\n          }\n          status\n          note\n          createdBy\n          createdAt\n          lastModifiedBy\n          lastModifiedAt\n        }\n        studentAttendance {\n          _id\n          student {\n            id\n            fullName\n            phoneNumber\n            email\n            gender\n            imageUrl\n            customer {\n              email\n            }\n          }\n          comment\n          sendCommentStatus\n          status\n          commentByAreas {\n            grade\n            content\n            commentAreaId\n          }\n          createdBy\n          createdAt\n          lastModifiedBy\n          lastModifiedAt\n        }\n        summary\n        homework\n        createdAt\n        createdBy\n        lastModifiedAt\n        lastModifiedBy\n      }\n      scheduleSettings {\n        _id\n        date\n        startTime\n        endTime\n        repeated\n      }\n      students {\n        _id\n        student {\n          id\n          fullName\n          phoneNumber\n          email\n          gender\n          dob\n          address\n          imageUrl\n          facebook\n          zalo\n          school\n          customer {\n            fullName\n            phoneNumber\n            email\n            facebook\n            zalo\n          }\n        }\n        note\n        activeInClass\n        createdBy\n        createdAt\n      }\n      teachers {\n        _id\n        teacher {\n          id\n          username\n          fullName\n          imageUrl\n          email\n          phoneNumber\n        }\n        role {\n          id\n          name\n          shortName\n          description\n          isActive\n        }\n        isActive\n      }\n      operator {\n        id\n        email\n        firstName\n        middleName\n        lastName\n        displayName\n        username\n      }\n      contactTeacher {\n        id\n        email\n        phoneNumber\n        fullName\n        code\n        username\n      }\n      hasSchedule\n      links {\n        _id\n        name\n        url\n      }\n      createdBy\n      createdAt\n      lastModifiedBy\n      lastModifiedAt\n    }\n  }\n}\n"
    //             }',
    //         CURLOPT_HTTPHEADER => [
    //             'Authorization: ' . $accessToken,
    //             'Content-Type: application/json',
    //         ],
    //     ]);

    //     $response = curl_exec($curl);
    //     if (curl_errno($curl)) {
    //         $error_msg = curl_error($curl);
    //     }

    //     curl_close($curl);

    //     if (isset($error_msg)) {
    //         throw new Exception($error_msg);
    //     }

    //     return $response;
    // }
    public static function getTeachers($token, $page = 1, $number = 10): object|array|null
    {
        $getTeachersEndpoint = "https://lms-api.mindx.vn/";

        $response = Http::withHeaders(
            [
                'Authorization' => $token,
            ]
        )->withBody('
                {
                    "operationName":"GetTeachers",
                    "variables":{
                        "type":"OFFSET",
                        "search":"",
                        "pageIndex":' . ($page - 1) . ',
                        "itemsPerPage":' . $number . ',
                        "orderBy":"createdAt_desc"
                    },
                    "query":"query GetTeachers($search: String, $isActive: Boolean, $courseLine: String, $course: String, $pageIndex: Int!, $itemsPerPage: Int!, $orderBy: String, $idNotIn: [String]) {\n  teachers(payload: {searchString_wordSearch: $search, isActive_eq: $isActive, courseLines_eq: $courseLine, courses_eq: $course, id_nin: $idNotIn, pageIndex: $pageIndex, itemsPerPage: $itemsPerPage, orderBy: $orderBy}) {\n    data {\n      id\n      username\n      user\n      firebaseId\n      fullName\n      code\n      phoneNumber\n      email\n      gender\n      dob\n      imageUrl\n      address\n      facebook\n      courseLines {\n        id\n        name\n      }\n      courses {\n        id\n        name\n        shortName\n        courseTopic {\n          id\n          name\n        }\n      }\n      notes\n      imageUrl\n      isActive\n      createdAt\n      createdBy\n      lastModifiedAt\n      lastModifiedBy\n    }\n    pagination {\n      type\n      total\n    }\n  }\n}\n"
                }
            ',
            'application/json'
        )->retry(3, 1000)->post($getTeachersEndpoint);

        return $response->object();
    }
}

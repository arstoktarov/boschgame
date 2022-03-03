<?php
/**
 * Created by PhpStorm.
 * User: Hp
 * Date: 04.07.2019
 * Time: 17:14
 */

namespace App\Push;


use App\Models\Webview;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FirebasePush
{
    const android = 'android';
    const ios = 'ios';

    public static function sendMessage($title, $body, $user, $additionalData = []) {
        if ($user && $user->device_token && $user->device_type) {
            $data = array(
                'title' => $title,
                'body' => $body,
                'sound' => 'default'
            );
            $data = array_merge($data, $additionalData);
            //return self::sendAndroid($user->device_token, $data);
            return self::sendMultiple([$user->device_token], $data, $user->device_type);
        }
        return ['error' => 'device_token or device_type or push has incorrect value'];
    }

    public static function send($to, $message) {
        $fields_android = array(
            'to'    => $to.'_a',
            'data'  => $message,
        );
        $fields_ios = array(
            'to'            => $to,
            'data'          => $message,
            'notification'  => $message,
        );

        return [
            'android'   => self::sendPushNotification($fields_android),
            'ios'       => self::sendPushNotification($fields_ios),
        ];
    }

    public static function sendAndroid($to, $message) {
        $fields = array(
            'to' => $to,
            'data' => $message,
        );
        return self::sendPushNotification($fields);
    }

// sending push message to multiple users by firebase registration ids
    public static function sendMultiple($registration_ids, $message, $device_type) {
        $fields = array(
            'registration_ids' => $registration_ids,
//            'data' => $message,
        );
        if ($device_type == self::android) {
            $fields['data'] = $message;
        }
        if ($device_type == self::ios) {
            $fields['notification'] = $message;
        }
        Log::info(print_r($fields, true));
        return self::sendPushNotification($fields);
    }

// function makes curl request to firebase servers
    private static function sendPushNotification($fields) {

        // Set POST variables
        $url = 'https://fcm.googleapis.com/fcm/send';
        $headers = array(
            'Authorization: key=AAAAta6ckMo:APA91bH-yVTtycIKxXu9Tmz3awLs-GTs5dZLePdonIzHVYyNrCIVkHYdbZs8NDDyte4-Uwd2q1WmzPEjxr58NC3fx4wHkWD9iUpkITt1F5eWHoI28KN6i1OKNlwOqb2bdMQiDIr6OsDV',
            'Content-Type: application/json'
        );
        // Open connection
        $ch = curl_init();

        // Set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

        // Execute post
        $result = curl_exec($ch);
        // echo "Result".$result;
        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }

        // Close connection
        curl_close($ch);

        return $result;
    }
}

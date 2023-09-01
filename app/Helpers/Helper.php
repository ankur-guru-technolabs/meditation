<?php

namespace App\Helpers;

use App\Models\Notification;
use App\Models\User;

class Helper
{
    public static function send_notification($notification_id, $sender_id = '', $receiver_id = '', $title = '', $type = '', $message = '', $custom = [])
    {
        $receiver_data = User::where('id', $receiver_id)->first();

        if ($notification_id == 'single') {
            $notification_id = [$receiver_data->fcm_token];
        }
       
        if (isset($custom['image'])) {
            $image = $custom['image'];
        } else {
            $image = asset('images/meditation.png');
        }

        if (!empty($receiver_data) && $receiver_data->is_notification_mute == 0 && $receiver_data->fcm_token != '') {
            $accesstoken = env('FCM_KEY');

            $data = [
                "registration_ids" => $notification_id,
                "notification" => [
                    "title" => $title,
                    // "body" => $message,  
                    "type" => $type,
                    "sender_id" => $sender_id,
                    "receiver_id" => $receiver_id,
                    "custom" => !empty($custom) ? json_encode($custom) : null,
                    "image" => $image,
                ],
                "data" => [
                    "title" => $title,
                    // "body" => $message,  
                    "type" => $type,
                    "sender_id" => $sender_id,
                    "receiver_id" => $receiver_id,
                    "custom" => !empty($custom) ? json_encode($custom) : null,
                    "image" => $image,
                ],
            ];
            $dataString = json_encode($data);

            $headers = [
                'Authorization:key=' . $accesstoken,
                'Content-Type: application/json',
            ];

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

            $response = curl_exec($ch);
        }
        $input['sender_id']     = $sender_id;
        $input['receiver_id']   = $receiver_id;
        $input['title']         = $title;
        $input['type']          = $type;
        $input['message']       = $message;
        $input['status']        = 0;
        $input['data']          = json_encode($custom);
        $notification_data      = Notification::create($input);
        return true;
    }

    public static function send_notification_by_admin($title = '',  $message = '', $custom = [])
    {
        $receivers = User::where('is_notification_mute', 0)
                    ->where('status',1)
                    ->whereNotNull('fcm_token')
                    ->get();

        $registration_ids = $receivers->pluck('fcm_token')->toArray();
        $receiverIds = $receivers->pluck('id')->toArray();

        if (empty($registration_ids)) {
            return false;  
        }

        $accesstoken = env('FCM_KEY');
        $image = isset($custom['image']) ? $custom['image'] : asset('images/meet-now.png');
        $data = [
            "registration_ids" => $registration_ids,
            "notification" => [
                "title" => $title,
                "type" => 'admin_notificaion',
                "sender_id" => 1,
                "custom" => !empty($custom) ? json_encode($custom) : null,
                "image" => $image,
            ],
            "data" => [
                "title" => $title,
                "type" => 'admin_notificaion',
                "sender_id" => 1,
                "custom" => !empty($custom) ? json_encode($custom) : null,
                "image" => $image,
            ],
        ];

        $dataString = json_encode($data);

        $headers = [
            'Authorization:key=' . $accesstoken,
            'Content-Type: application/json',
        ];
    
        $ch = curl_init();
    
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
    
        $response = curl_exec($ch);

        $commonNotificationData = [
            'sender_id' => 1,
            'title' => $title,
            'type' => 'admin_notification',
            'message' => $message,
            'status' => 0,
            'data' => json_encode($custom),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $notificationData = array_map(function ($receiverId) use ($commonNotificationData) {
            return array_merge(['receiver_id' => $receiverId], $commonNotificationData);
        }, $receiverIds);

        Notification::insert($notificationData);
        
        return true;
    }
}

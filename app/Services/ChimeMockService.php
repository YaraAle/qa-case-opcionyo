<?php

namespace App\Services;


class ChimeMockService
{

    public function createMeeting()
    {

        return [

            'meeting_id' => 'mock-meeting-001',

            'status' => 'created'

        ];

    }


    public function joinMeeting($user)
    {

        return [

            'user' => $user,

            'audio' => true,

            'video' => true,

            'status' => 'joined'

        ];

    }


    public function leaveMeeting($user)
    {

        return [

            'user' => $user,

            'status' => 'left'

        ];

    }

}
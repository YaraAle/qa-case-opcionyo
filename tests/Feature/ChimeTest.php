<?php

use App\Services\ChimeMockService;


test('usuario puede crear una reunion de videollamada', function () {

    $chime = new ChimeMockService();


    $meeting = $chime->createMeeting();


    expect($meeting['status'])
        ->toBe('created');


    expect($meeting)
        ->toHaveKey('meeting_id');

});



test('usuario puede unirse a una videollamada', function () {

    $chime = new ChimeMockService();


    $participant = $chime->joinMeeting('paciente@test.com');


    expect($participant['status'])
        ->toBe('joined');


    expect($participant['audio'])
        ->toBeTrue();


    expect($participant['video'])
        ->toBeTrue();

});



test('usuario puede abandonar una videollamada', function () {

    $chime = new ChimeMockService();


    $result = $chime->leaveMeeting('paciente@test.com');


    expect($result['status'])
        ->toBe('left');

});
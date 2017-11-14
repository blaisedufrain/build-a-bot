<?php

namespace BuildABot\App;

use iansltx\DialogflowBridge\Answer;
use iansltx\DialogflowBridge\HandlerInterface;
use iansltx\DialogflowBridge\Question;
use iansltx\JoindInClient\Client;
use iansltx\JoindInClient\NoMoreEventsException;

class ScheduleHandler implements HandlerInterface
{
    protected $scheduleClient;

    public function __construct(Client $scheduleClient)
    {
        $this->scheduleClient = $scheduleClient;
    }

    public function __invoke(Question $question, Answer $answer): Answer
    {
        date_default_timezone_set('America/New_York');
        $dateParam = $question->getParam('date', date('Y-m-d'));

        $filteredSchedule = $this->scheduleClient->getScheduleByEventId(6476)->filterOutBefore(
            $after = ($dateParam === date('Y-m-d') ?
                new \DateTimeImmutable() :
                \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $dateParam . ' 00:00:00'))
        );

        try {
            return $answer->withSpeechAndText('The next event is ' . $filteredSchedule->first() . '.');
        } catch (NoMoreEventsException $e) {
            return $answer->withSpeechAndText("There are no PHP World events on or after the " .
                $after->format('jS') . '.');
        }
    }
}
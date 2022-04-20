<?php

namespace Modules\OuterServices\Services\Sources;

use App\Models\Application;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use UnexpectedValueException;

/**
 * Class Marquiz
 * @package Modules\OuterServices\Services\Sources
 */
class Marquiz implements Source
{
    /**
     * @param array $data
     * @return \App\Models\Application
     * @throws \Throwable
     */
    public function convertApplication(array $data): Application
    {
        if (!Arr::has($data, 'contacts.email')) {
            $quizName = Arr::get($data, 'quiz.name');
            throw new UnexpectedValueException("No email from Marquiz ({$quizName})");
        }

        $application = new Application;
        $application->status = Application::CONFIRMED_STATUS;
        $application->submitted_at = Carbon::createFromTimeString(Arr::get($data, 'created'));
        $application->site_id = 1; // academyfx.ru

        $application->form_name = Arr::get($data, 'quiz.name', 'Лид с Marquiz');
        $application->sender_name = Arr::get($data, 'contacts.name', 'Контакт с Marquiz');
        $application->sender_phone = Arr::get($data, 'contacts.phone');
        $application->sender_email = Arr::get($data, 'contacts.email');

        $application->page_url = Arr::get($data, 'extra.href');
        $application->utm_content = Arr::get($data, 'extra.utm.content');
        $application->utm_source = Arr::get($data, 'extra.utm.source');
        $application->utm_medium = Arr::get($data, 'extra.utm.medium');
        $application->utm_campaign = Arr::get($data, 'extra.utm.name');

        $quizResults = null;
        foreach (Arr::get($data, 'answers') as $question) {
            $answer = is_array($question['a'])
                ? implode(', ', $question['a'])
                : $question['a'];
            $quizResults .= "<b>Вопрос:</b> {$question['q']}; <b>Ответ</b>: {$answer};<br>";
        }
        $application->comment = $quizResults;

        return $application;
    }
}
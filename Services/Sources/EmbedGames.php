<?php

namespace Modules\OuterServices\Services\Sources;

use App\Models\Application;
use App\Models\Site;
use Faker\Factory;
use Faker\Generator;
use Faker\Provider\ru_RU\Internet;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator as ValidatorFactory;
use Illuminate\Validation\Validator;
use UnexpectedValueException;

/**
 * Class EmbedGames
 * @package Modules\OuterServices\Services\Sources
 */
class EmbedGames implements Source
{
    /**
     * @var \Illuminate\Validation\Validator
     */
    protected $validator;

    /**
     * @param array $data
     * @return \App\Models\Application|null
     * @throws \Throwable
     */
    public function convertApplication(array $data): ?Application
    {
        $validationPassed = $this->getValidator($data)->passes();
        if (!$validationPassed) {
            $errors = $this->getValidator()->errors()->toJson();
            throw new UnexpectedValueException("Невалидные данные от embedgames: {$errors}");
        }

        $application = new Application;
        $application->status = Application::CONFIRMED_STATUS;

        $rawDate = $data['DATE'];
        $formattedDate = trim(preg_replace("/[^0-9.: ]/", '', $rawDate));
        $application->submitted_at = Carbon::createFromTimeString($formattedDate);

        $siteId = Site::where('publish', true)->orderBy('id')->value('id');
        $application->site_id = $siteId;

        $application->form_name = 'Колесо Фортуны';
        $application->sender_name = "Лид Колеса Фортуны ({$data['PHONE']})";

        $faker = Factory::create('ru_RU');
        $application->sender_email = 'fake.' . $faker->unique()->safeEmail;
        $application->sender_phone = $data['PHONE'];

        $application->page_url = $data['HREF'];
        $application->utm_content = Arr::get($data, 'UTM_CONTENT');
        $application->utm_source = Arr::get($data, 'UTM_SOURCE');
        $application->utm_medium = Arr::get($data, 'UTM_MEDIUM');
        $application->utm_campaign = Arr::get($data, 'UTM_CAMPAIGN');

        $application->comment = "Выигранный приз: {$data['COMMENTS']}";

        return $application;
    }

    /**
     * @param array|null $data
     * @return \Illuminate\Validation\Validator
     */
    protected function getValidator(array $data = null): Validator
    {
        if ($this->validator instanceof Validator) {
            return $this->validator;
        }

        $this->validator = ValidatorFactory::make($data, [
            'PHONE' => ['required', 'phone:AUTO,RU'],
            'COMMENTS' => ['required', 'string'],
            'HREF' => ['required', 'url'],
            'UTM_SOURCE' => ['nullable', 'string'],
            'UTM_MEDIUM' => ['nullable', 'string'],
            'UTM_CAMPAIGN' => ['nullable', 'string'],
            'UTM_CONTENT' => ['nullable', 'string'],
            'DATE' => ['required', 'string'],
        ]);

        return $this->validator;
    }
}
<?php

namespace Modules\OuterServices\Services\Sources;

use App\Helpers\AddLead\Field\Phone;
use App\Models\Application;
use App\Models\Site;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator as ValidatorFactory;
use Illuminate\Validation\Validator;
use UnexpectedValueException;

class Creatium implements Source
{
    /**
     * @var \Illuminate\Validation\Validator
     */
    protected $validator;

    /**
     * @param array $data
     * @return \App\Models\Application|null
     */
    public function convertApplication(array $data): ?Application
    {
        $validationPassed = $this->getValidator($data)->passes();
        if (!$validationPassed) {
            $errors = $this->getValidator()->errors()->toJson();
            throw new UnexpectedValueException("Невалидные данные от creatium: {$errors}");
        }

        $application = new Application;
        $application->status = Application::CONFIRMED_STATUS;
        $application->submitted_at = Carbon::now();

        $siteId = Site::where('publish', true)->orderBy('id')->value('id');
        $application->site_id = $siteId;
        $application->form_name = $data['order']['form_name'];

        $name = null; $email = null; $phone = null;
        foreach ($data['order']['fields_by_name'] as $fieldName => $fieldValue) {
            if ($fieldName == 'Имя') { $name = $fieldValue; continue; }
            if ($fieldName == 'Номер телефона') { $phone = $fieldValue; continue; }
            if ($fieldName == 'Электронная почта') { $email = $fieldValue; }
        }

        $application->sender_name = $name ?? 'Заявка с Creatium';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new UnexpectedValueException('Невалидный email');
        }
        $application->sender_email = $email;

        $phone = Phone::clear($phone);
        if (mb_strlen($phone) < 10) {
            throw new UnexpectedValueException('Невалидный телефон');
        }
        $application->sender_phone = $phone;

        $application->page_url = $data['page']['url'];
        $application->page_title = $data['page']['name'];
        $application->utm_content = Arr::get($data, 'visit.utm_content');
        $application->utm_source = Arr::get($data, 'visit.utm_source');
        $application->utm_medium = Arr::get($data, 'visit.utm_medium');
        $application->utm_campaign = Arr::get($data, 'visit.utm_campaign');

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
            'order.form_name' => ['required', 'string'],
            'order.fields_by_name' => ['required', 'array', 'min:3'],
            'page.url' => ['required', 'url'],
            'page.name' => ['required', 'string'],
            'visit.utm_source' => ['nullable', 'string'],
            'visit.utm_medium' => ['nullable', 'string'],
            'visit.utm_campaign' => ['nullable', 'string'],
            'visit.utm_content' => ['nullable', 'string'],
        ]);

        return $this->validator;
    }
}
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadDocumentsRequest extends FormRequest
{
    public function rules(): array
    {
        $maxKb = (int) config('aparcado.uploads.max_kilobytes');

        return [
            'document_photo' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', "max:{$maxKb}"],
            'licence_photo' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', "max:{$maxKb}"],
        ];
    }

    public function attributes(): array
    {
        return [
            'document_photo' => 'la foto del documento',
            'licence_photo' => 'la foto del carné',
        ];
    }
}

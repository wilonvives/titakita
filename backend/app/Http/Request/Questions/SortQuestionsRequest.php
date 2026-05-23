<?php

namespace TitaKita\Http\Request\Questions;

use TitaKita\Http\Request\BaseRequest;

class SortQuestionsRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            '*.id' => 'integer|required',
            '*.order' => 'integer|required',
        ];
    }
}

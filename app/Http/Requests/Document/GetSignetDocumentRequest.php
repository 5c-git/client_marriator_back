<?php

namespace App\Http\Requests\Document;

use App\Enum\Document\DocumentStatusEnum;
use App\Enum\Document\DocumentStatusSignatureEnum;
use App\Enum\Order\BidAcceptingStatusEnum;
use App\Enum\Order\OrderStatusEnum;
use App\Http\Requests\FormRequest;
use App\Models\Document\Document;
use App\Models\Order\Bid;
use App\Models\Order\Task;
use App\Models\User;
use Illuminate\Validation\Rule;
use App\Models\Order\Order;
/**
 * @property-read int documentId
 */
class GetSignetDocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'documentId' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $user = auth()->user();
                    $document = Document::query()
                        ->where('id', $value)
                        ->where('user_id', $user->id)
                        ->where('status', DocumentStatusEnum::Signed->value)
                        ->where('status_signature', DocumentStatusSignatureEnum::Signed->value)
                        ->first();

                    if (!$document) {
                        $fail('Document not found or not signed');
                    }
                },
            ],
        ];
    }
}

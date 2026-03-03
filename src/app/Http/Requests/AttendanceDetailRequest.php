<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AttendanceDetailRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
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
            'clock_in' => ['required', 'date_format:H:i'],
            'clock_out' => ['required', 'date_format:H:i'],
            'breaks.*.start' => ['nullable', 'date_format:H:i'],
            'breaks.*.end' => ['nullable', 'date_format:H:i'],
            'remark' => ['required', 'string'],
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {

        $data = $this->all();
        $clockIn = $data['clock_in'] ?? null;
        $clockOut = $data['clock_out'] ?? null;

        // 出勤退勤チェック（共通エラー）
        if ($clockIn && $clockOut && strtotime($clockOut) < strtotime($clockIn)) {
            $validator->errors()->add(
                'attendance_time',
                '出勤時間もしくは退勤時間が不適切な値です'
            );
        }

        // 休憩チェック
        if (!empty($data['breaks'])) {
            foreach ($data['breaks'] as $index => $break) {
                $start = $break['start'] ?? null;
                $end = $break['end'] ?? null;

                // 休憩開始 > 休憩終了
                if ($start && $end && strtotime($end) <= strtotime($start)) {
                    $validator->errors()->add(
                        "breaks.$index",
                        '休憩時間が不適切な値です'
                    );
                    continue; // 他のチェックはスキップして重複防止
                }

                // 休憩開始が出勤前 or 退勤後
                if ($start) {
                    if ($clockIn && strtotime($start) < strtotime($clockIn)) {
                        $validator->errors()->add("breaks.$index", '休憩時間が不適切な値です');
                    } elseif ($clockOut && strtotime($start) > strtotime($clockOut)) {
                        $validator->errors()->add("breaks.$index", '休憩時間が不適切な値です');
                    }
                }

                // 休憩終了が退勤後
                if ($end) {
                    if ($clockOut && strtotime($end) > strtotime($clockOut)) {
                        $validator->errors()->add("breaks.$index", '休憩時間もしくは退勤時間が不適切な値です');
                    }
                }
            }
        }
    });
    }

    public function messages(): array
    {
        return [
            'clock_in.required' => '出勤時間を入力してください',
            'clock_out.required' => '退勤時間を入力してください',
            'remark.required' => '備考を記入してください',
        ];
    }

}

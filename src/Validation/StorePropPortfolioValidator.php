<?php

namespace Nukeflame\Webmatics\Validation;

class StorePropPortfolioValidator
{
    public static function rules(int $currentYear): array
    {
        return [
            'cover_no' => 'required|string|max:50|exists:cover_register,cover_no',
            'type_of_bus' => 'required|string|exists:business_types,bus_type_id',
            'orig_endorsement' => 'required|string|max:50|exists:cover_register,endorsement_no',
            'posting_year' => 'required|integer|min:' . ($currentYear - 2) . '|max:' . ($currentYear + 1),
            'posting_date' => 'required|date',
            'currency_code' => 'required|string|max:10|exists:currency,currency_code',
            'today_currency' => 'required|numeric|min:0.000001',
            'portfolio_type' => 'required|string|in:IN,OUT',
            'port_prem_rate' => 'required|numeric|min:0|max:100',
            'port_loss_rate' => 'required|numeric|min:0|max:100',
            'port_premium_amt' => ['required', 'string', 'regex:/^\d+(,\d{3})*(\.\d+)?$|^\d+(\.\d+)?$/'],
            'port_outstanding_loss_amt' => ['required', 'string', 'regex:/^\d+(,\d{3})*(\.\d+)?$|^\d+(\.\d+)?$/'],
            'port_amt' => ['nullable', 'string', 'regex:/^\d+(,\d{3})*(\.\d+)?$|^\d+(\.\d+)?$/'],
            'port_reinsurer' => 'nullable|string|max:50|exists:customers,customer_id',
            'port_share' => 'nullable|numeric|min:0|max:100',
            'comments' => 'nullable|string|max:2000',
            'show_cedant' => 'nullable|boolean',
            'show_reinsurer' => 'nullable|boolean',
        ];
    }

    public static function messages(): array
    {
        return [
            'cover_no.required' => 'Cover number is required.',
            'cover_no.exists' => 'The selected cover number does not exist.',
            'type_of_bus.required' => 'Business type is required.',
            'type_of_bus.exists' => 'The selected business type is invalid.',
            'orig_endorsement.required' => 'Original endorsement number is required.',
            'orig_endorsement.exists' => 'The selected original endorsement does not exist.',
            'posting_year.required' => 'Underwriting year is required.',
            'posting_year.integer' => 'Underwriting year must be a valid year.',
            'posting_year.min' => 'Underwriting year is too far in the past.',
            'posting_year.max' => 'Underwriting year cannot be more than one year ahead.',
            'posting_date.required' => 'Posting date is required.',
            'posting_date.date' => 'Posting date must be a valid date.',
            'currency_code.required' => 'Currency is required.',
            'currency_code.exists' => 'The selected currency is invalid.',
            'today_currency.required' => 'Exchange rate is required.',
            'today_currency.numeric' => 'Exchange rate must be numeric.',
            'today_currency.min' => 'Exchange rate must be greater than 0.',
            'portfolio_type.required' => 'Portfolio type is required.',
            'portfolio_type.in' => 'Portfolio type must be either IN or OUT.',
            'port_prem_rate.required' => 'Premium rate is required.',
            'port_prem_rate.numeric' => 'Premium rate must be numeric.',
            'port_prem_rate.min' => 'Premium rate cannot be negative.',
            'port_prem_rate.max' => 'Premium rate cannot exceed 100.',
            'port_loss_rate.required' => 'Loss rate is required.',
            'port_loss_rate.numeric' => 'Loss rate must be numeric.',
            'port_loss_rate.min' => 'Loss rate cannot be negative.',
            'port_loss_rate.max' => 'Loss rate cannot exceed 100.',
            'port_premium_amt.required' => 'Premium amount is required.',
            'port_premium_amt.regex' => 'Premium amount must be a valid number.',
            'port_outstanding_loss_amt.required' => 'Outstanding loss amount is required.',
            'port_outstanding_loss_amt.regex' => 'Outstanding loss amount must be a valid number.',
            'port_amt.regex' => 'Portfolio amount must be a valid number.',
            'port_reinsurer.exists' => 'The selected reinsurer does not exist.',
            'port_share.numeric' => 'Reinsurer share must be numeric.',
            'port_share.min' => 'Reinsurer share cannot be negative.',
            'port_share.max' => 'Reinsurer share cannot exceed 100.',
            'comments.max' => 'Comments cannot exceed 2000 characters.',
            'show_cedant.boolean' => 'Show cedant must be true or false.',
            'show_reinsurer.boolean' => 'Show reinsurer must be true or false.',
        ];
    }
}

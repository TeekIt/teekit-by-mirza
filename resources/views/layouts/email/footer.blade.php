<style>
    .mail-footer {
        margin:21px !important;
    }

    .mail-footer > p {
        line-height: 21px !important;
        text-align: center !important;
    }
</style>
{{-- 
    Note:
    Please don't add indentation in the following code to 
    prevent any sort of UI issues in the mail templates.
 --}}
<center>
<img src="{{ asset('teekit.png') }}">
</center>

<div class="mail-footer">
<p>{{ config('constants.ADMIN_EMAIL') }} | {{ config('constants.HEAD_OFFICE_CONTACT') }}</p>
<p>{{ config('constants.HEAD_OFFICE_ADDRESS') }}</p>
</div>
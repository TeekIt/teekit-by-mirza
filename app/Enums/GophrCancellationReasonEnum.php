<?php

namespace App\Enums;

enum GophrCancellationReasonEnum: string
{
    /**
     * The following reasons are officially provided by Gophr
     * Reference of the document: https://developers.gophr.com/reference/post-jobs-job_id-cancel
     */
    case ORDER_CANCELLED = 'Customer cancels order.';
    case WRONG_INFO = 'Incorrect job details e.g. incorrect vehicle type requested, or booking doesn\'t reflect correct number of boxes etc.';
    case COURIER_LATE = 'The courier is running late.';
    case DUPLICATED = 'Double booked by mistake.';
    case FRAUDULENT_ORDER = 'Fraudulent Order.';
    case NOT_ACCEPTED = 'Job not accepted by courier.';
    case NRT = 'Not received in trunking.';
    case RESCHEDULED = 'Rescheduled for later delivery.';
    case TECHNICAL_ISSUES = 'Technical issues with delivery.';
    case TEST_ORDER = 'Test order.';
    case NO_NEED = 'No need.';
}

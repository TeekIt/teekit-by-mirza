<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Custom File For Storing Messages Constants
    |--------------------------------------------------------------------------
    |
    | This file contains all of the constants which are used in our application
    | to display any kind of messages
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Success Constants
    |--------------------------------------------------------------------------
    */
    'UPDATION_SUCCESS' => 'Data updated successfully',
    'ARCHIVED_SUCCESS' => 'Data archived successfully',
    'UN_ARCHIVED_SUCCESS' => 'Data unarchived successfull',
    'DELETION_SUCCESS' => 'Data deleted successfully',
    'USERS_DELETION_SUCCESS' => 'Users deleted successfully',
    'DRIVERS_DELETION_SUCCESS' => 'Drivers deleted successfully',
    'LOGIN_SUCCESS' => 'You have logged in successfully',
    'REGISTER_SUCCESS' => 'You have registered successfully',
    'DRIVER_REGISTERATION_MSG' => 'You have registered successfully. We have sent you a verification email please verify.',
    'DATA_INSERTION_SUCCESS' => 'Data inserted successfully',
    'DATA_UPDATED_SUCCESS' => 'Data updated successfully',
    'ORDER_CANCELLATION_SUCCESS' => 'Your order has been cancelled successfully',
    'SUCCESS_CODE' => 1,
    'SUCCESS_STATUS' => 'success',
    'TRUE_STATUS' => true,
    'STUART_DELIVERY_SUCCESS' => 'Stuart delivery has been initiated successfully, You can please check the status by clicking the "Check Status" button',
    'COMPLETED' => 'Completed',
    'VALID_REFERRAL' => 'Valid referral code',
    'ORDER_ASSIGNED' => 'Assigned',
    'ORDER_UPDATED' => 'Updated',
    'VALID_PROMOCODE' => 'You have entered a valid promo code',
    'BANK_DETAILS_UPDATED' => 'Bank Account details are successfully updated',
    'VERIFICATION_SUCCESS' => 'Verification Successful',
    'CACHE_REMOVED_SUCCESSFULLY' => 'All Cached data of your App has been removed successfully',
    'WITHDRAWAL_REQUEST_SUBMITTED' => 'Withdrawal request is successfully submitted',
    'PRODUCT_REMOVED_SUCCESSFULLY' => 'Product has been removed successfully',
    'ORDER_PLACED_SUCCESSFULLY' => 'Your order has been placed successfully',
    'SENT_TO_OTHER_STORE_SUCCESS' => 'Your order has been sent to other store',
    'PARENT_QTY_SYNCED_SUCCESS' => 'Quantities synced',
    /*
    |--------------------------------------------------------------------------
    | Failure Constants
    |--------------------------------------------------------------------------
    */
    'REGISTER_FAILED' => 'Sorry there is an error while your registeration process',
    'INSERTION_FAILED' => 'Failed to insert data',
    'UPDATION_FAILED' => 'Failed to update data',
    'ARCHIVED_FAILED' => 'Failed to archive data',
    'UN_ARCHIVED_FAILED' => 'Failed to unarchive data',
    'DELETION_FAILED' => 'Failed to delete data',
    'INVALID_DATA' => 'You have entered invalid or too long data',
    'ORDER_CANCELLATION_FAILED' => 'Sorry! We are facing an error in cancelling your order',
    'FAILED_CODE' => 0,
    'FALSE_STATUS' => false,
    'ERROR_STATUS' => 'error',
    'STATUS_CHANGING_FAILED' => 'Failed to change the activation status',
    'INVALID_REFERRAL' => 'Invalid referral code',
    'REFERRAL_CAN_BE_USED_ONCE' => 'Referral code can be used only once',
    'REFERRALS_ARE_ONLY_FOR_FIRST_ORDER' => 'Sorry, you can only use a referral before placing your first order',
    'VALIDATION_ERROR' => 'Validation Error',
    'INVALID_CREDENTIALS' => 'Invalid Credentials',
    'EMAIL_NOT_VERIFIED' => 'Email not verified, verify your email first',
    'ACCOUNT_DEACTIVATED' => 'Your account has been deactivated, Please contact the admin',
    'NO_RECORD' => 'No Record Found',
    'NO_SELLER' => 'No seller found against this id',
    'NO_STORES_FOUND' => 'No stores found in this area',
    'ORDER_CANCELLED' => 'Cancelled',
    'INVALID_PROMOCODE' => 'Invalid promo code',
    'EXPIRED_PROMOCODE' => 'This promo code has been expired',
    'MISSING_OR_INVALID_DATA' => 'Required fields missing or invalid data',
    'VERIFICATION_FAILED' => 'You have entered a invalid verification code',
    'ITEM_DELETED' => 'Data deleted successfully',
    'MAX_LIMIT' => 'Promo code usage has reached its maximum limit',
    'DATA_ALREADY_EXISTS' => 'Data already exists against id:- ',
    'CACHE_REMOVED_FAILED' => 'Sorry due to some issue your cahce can not be removed',
    'QTY_SHOULD_NOT_BE_GREATER' => 'You cannot enter quantity more then your stock',
    'PRODUCT_REMOVED_FAILED' => 'There is an error while removing the product',
    'SENT_TO_OTHER_STORE_FAILED' => 'Due to some error your order cannot be sent to other store',
    'PARENT_QTY_SYNCED_FAILED' => 'Failed to sync quantities',
    'INTERNAL_SERVER_ERROR'=> 'Sorry! This operation has been failed due to some internal server error',
    /*
    |--------------------------------------------------------------------------
    | General Messages Constants
    |--------------------------------------------------------------------------
    */
    'PARENT_QTY_ALREADY_SYNCED' => 'Quantities of parent seller producs are already synced',
    /*
    |--------------------------------------------------------------------------
    | Digital Ocean Bucket
    |--------------------------------------------------------------------------
    */
    'BUCKET' => 'https://user-imgs.sgp1.digitaloceanspaces.com/',
    /*
    |--------------------------------------------------------------------------
    | Admin Email
    |--------------------------------------------------------------------------
    */
    'ADMIN_EMAIL' => 'admin@teekit.co.uk',
    /*
    |--------------------------------------------------------------------------
    | Teek it URL's
    |--------------------------------------------------------------------------
    */
    'LIVE_DASHBOARD_URL' => 'https://app.teekit.co.uk',
    'APIS_DOMAIN_URL' => 'https://teekitapi.com',
    /*
    |--------------------------------------------------------------------------
    | HTTP Status Codes
    |--------------------------------------------------------------------------
    */
    'HTTP_OK' => 200,
    'HTTP_SERVER_ERROR' => 500,
    'HTTP_INVALID_ARGUMETS' => 400,
    'HTTP_FORBIDDEN' => 403,
    'HTTP_UNPROCESSABLE_REQUEST' => 422,
    'HTTP_RESOURCE_EXHAUSTED' => 429,
    'HTTP_PAGE_EXPIRED' => 419,
    'HTTP_SERVICE_UNAVAILABLE' => 503,
    'HTTP_GATEWAY_TIMEOUT' => 504,
];

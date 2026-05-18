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
    | Globally Required
    |--------------------------------------------------------------------------
    */
    'EXTRA_CHARGE_AMOUNT' => 5.00,

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
    'DRIVER_REGISTERATION_MSG' => 'You have registered successfully. We have sent you a verification email please verify',
    'DATA_INSERTION_SUCCESS' => 'Data inserted successfully',
    'DATA_UPDATED_SUCCESS' => 'Data updated successfully',
    'ORDER_CANCELLATION_SUCCESS' => 'Your order has been cancelled successfully',
    'SUCCESS_CODE' => 1,
    'SUCCESS_STATUS' => 'success',
    'TRUE_STATUS' => true,
    'STUART_DELIVERY_SUCCESS' => 'Stuart delivery has been initiated successfully, You can please check the status by clicking the "Check Status" button',
    'DELIVERY_SUCCESS' => 'Your order delivery has been initiated successfully, You can please check the status by clicking the "Track Delivery" button',
    'COMPLETED' => 'Completed',
    'VALID_REFERRAL' => 'Valid referral code',
    'ORDER_ASSIGNED' => 'Assigned',
    'ORDER_UPDATED' => 'Updated',
    'ORDER_DELIVERED_SUCCESSFULLY' => 'You have successfully delivered your order 🎉',
    'ORDER_COMPLETED_SUCCESSFULLY' => 'Your order has been completed successfully',
    'ORDER_ACCEPTED_SUCCESSFULLY' => 'Order has been accepted successfully',
    'VALID_PROMOCODE' => 'You have entered a valid promo code',
    'BANK_DETAILS_UPDATED' => 'Bank Account details are successfully updated',
    'VERIFICATION_SUCCESS' => 'Verification Successful',
    'CACHE_REMOVED_SUCCESSFULLY' => 'All Cached data of your App has been removed successfully',
    'WITHDRAWAL_REQUEST_SUBMITTED' => 'Withdrawal request is successfully submitted',
    'PRODUCT_REMOVED_SUCCESSFULLY' => 'Product has been removed successfully',
    'ORDER_PLACED_SUCCESSFULLY' => 'Your order has been placed successfully',
    'PAY_AS_YOU_GO_ORDER_PLACED_SUCCESSFULLY' => 'Pay as you go products added to the Van successfully. You can pay for them after use',
    'SENT_TO_OTHER_STORE_SUCCESS' => 'Your order has been sent to another store',
    'PARENT_QTY_SYNCED_SUCCESS' => 'Quantities synced',
    /* Van Management System */
    'VAN_ORDER_COMPLETED_SUCCESSFULLY' => 'Order completed, products moved to van inventory successfully',
    /*
    |--------------------------------------------------------------------------
    | Failure Constants
    |--------------------------------------------------------------------------
    */
    'ORDER_PLACED_FAILED' => 'Sorry! Due to some error your order could not be placed.',
    'REGISTER_FAILED' => 'Sorry there is an error while your registeration process',
    'INSERTION_FAILED' => 'Failed to insert data',
    'UPDATION_FAILED' => 'Failed to update data',
    'ARCHIVED_FAILED' => 'Failed to archive data',
    'UN_ARCHIVED_FAILED' => 'Failed to unarchive data',
    'DELETION_FAILED' => 'Failed to delete data',
    'INVALID_DATA' => 'You have entered invalid or too long data',
    'ORDER_CANCELLATION_FAILED' => 'Sorry! We regret that we are facing an error in cancelling your order',
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
    'NO_NEAR_BY_SELLERS' => 'No nearby sellers found',
    'NO_STORES_FOUND' => 'No stores found in this area',
    'ORDER_CANCELLED' => 'Cancelled',
    'INVALID_PROMOCODE' => 'Invalid promo code',
    'EXPIRED_PROMOCODE' => 'This promo code has been expired',
    'MISSING_OR_INVALID_DATA' => 'Required fields missing or invalid data',
    'VERIFICATION_FAILED' => 'You have entered a invalid verification code',
    'ITEM_DELETED' => 'Data deleted successfully',
    'PROMOCODE_REACHED_MAX_LIMIT' => 'Promo code usage has reached its maximum limit',
    'DATA_ALREADY_EXISTS' => 'Data already exists against id:- ',
    'CACHE_REMOVED_FAILED' => 'Sorry due to some issue your cahce can not be removed',
    'QTY_SHOULD_NOT_BE_GREATER' => 'You cannot enter quantity more then your stock',
    'PRODUCT_REMOVED_FAILED' => 'There is an error while removing the product',
    'SENT_TO_OTHER_STORE_FAILED' => 'Due to some error your order cannot be sent to other store',
    'PARENT_QTY_SYNCED_FAILED' => 'Failed to sync quantities',
    'INTERNAL_SERVER_ERROR' => 'Sorry! This operation has been failed due to some internal server error',
    'DELIVERY_FAILED' => 'Sorry! Due to some internal error we are failed to initiate your delivery',
    'SEARCH_FAILED' => 'Sorry! We could not find any results against your search',
    'IMPORT_FAILED' => 'Sorry! Due to some internal error your import has been failed',
    'EXPORT_FAILED' => 'Sorry! Due to some internal error your export has been failed',
    'UNAUTHORIZED_ACTION' => 'This action is unauthorized for you because this resource belongs to someone else',
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
    'BUCKET' => 'https://teekit-production-bucket.lon1.digitaloceanspaces.com/',
    /*
    |--------------------------------------------------------------------------
    | Admin Email
    |--------------------------------------------------------------------------
    */
    'ADMIN_EMAIL' => env('ADMIN_EMAIL'),
    /*
    |--------------------------------------------------------------------------
    | Head Office Details
    |--------------------------------------------------------------------------
    */
    'HEAD_OFFICE_ADDRESS' => '1 Waldegrave Road, Ealing, W5 3HT',
    'HEAD_OFFICE_CONTACT' => '+44 0208 998 0315',
    /*
    |--------------------------------------------------------------------------
    | Teek it URL's
    |--------------------------------------------------------------------------
    */
    'LIVE_WEBSITE_URL' => 'https://teekit.co.uk',
    'LIVE_DASHBOARD_URL' => 'https://app.teekit.co.uk',
    /*
    |--------------------------------------------------------------------------
    | HTTP Status Codes
    |--------------------------------------------------------------------------
    */
    'HTTP_OK' => 200,
    'HTTP_INVALID_ARGUMETS' => 400,
    'HTTP_UNAUTHORIZED' => 401,
    'HTTP_FORBIDDEN' => 403,
    'HTTP_NOT_FOUND' => 404,
    'HTTP_PAGE_EXPIRED' => 419,
    'HTTP_UNPROCESSABLE_REQUEST' => 422,
    'HTTP_RESOURCE_EXHAUSTED' => 429,
    'HTTP_SERVER_ERROR' => 500,
    'HTTP_SERVICE_UNAVAILABLE' => 503,
    'HTTP_GATEWAY_TIMEOUT' => 504,
];

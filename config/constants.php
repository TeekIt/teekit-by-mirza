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
    'INSERTION_SUCCESS' => 'Data inserted successfully.',
    'UPDATION_SUCCESS' => 'Data updated successfully.',
    'ARCHIVED_SUCCESS' => 'Data archived successfully.',
    'UN_ARCHIVED_SUCCESS' => 'Data unarchived successfully.',
    'DELETION_SUCCESS' => 'Data deleted successfully.',
    'LOGIN_SUCCESS' => 'You have Logged in Successfully.',
    'REGISTER_SUCCESS' => 'You have Registered Successfully.',
    'DRIVER_REGISTERATION_MSG' => 'You have registered successfully. We have sent you a verification email please verify.',
    'DATA_INSERTION_SUCCESS' => 'Data Inserted Successfully.',
    'DATA_UPDATED_SUCCESS' => 'Data Updated Successfully.',
    'ORDER_CANCELLATION_SUCCESS' => 'Your order has been cancelled successfully.',
    'SUCCESS_CODE' => 1,
    'TRUE_STATUS' => true,
    'STUART_DELIVERY_SUCCESS' => 'Stuart Delivery Has Been Initiated Successfully, You Can Please Check The Status By Clicking The "Check Status" Button',
    'COMPLETED' => 'Completed',
    'VALID_REFERRAL' => 'Valid referral code.',
    'ORDER_ASSIGNED' => 'Assigned.',
    'ORDER_UPDATED' => 'Updated.',
    'VALID_PROMOCODE' => 'You have entered a valid promo code.',
    'BANK_DETAILS_UPDATED' => 'Bank Account details are successfully updated.',
    'VERIFICATION_SUCCESS' => 'Verification Successful.',
    'CACHE_REMOVED_SUCCESSFULLY' => 'All Cached data of your App has been removed successfully.',
    'WITHDRAWAL_REQUEST_SUBMITTED' => 'Withdrawal request is successfully submitted.',
    /*
    |--------------------------------------------------------------------------
    | Failure Constants
    |--------------------------------------------------------------------------
    */
    'REGISTER_FAILED' => 'Sorry there is an error while your registeration process.',
    'INSERTION_FAILED' => 'Failed to insert data.',
    'UPDATION_FAILED' => 'Failed to update data.',
    'ARCHIVED_FAILED' => 'Failed to archive data.',
    'UN_ARCHIVED_FAILED' => 'Failed to unarchive data.',
    'DELETION_FAILED' => 'Failed to delete data.',
    'INVALID_DATA' => 'You have entered invalid or too long data.',
    'ORDER_CANCELLATION_FAILED' => 'Sorry! We are facing an error in cancelling your order.',
    'FAILED_CODE' => 0,
    'FALSE_STATUS' => false,
    'STATUS_CHANGING_FAILED' => 'Failed to change the activation status.',
    'INVALID_REFERRAL' => 'Invalid referral code.',
    'REFERRAL_CAN_BE_USED_ONCE' => 'Referral code can be used only once.',
    'REFERRALS_ARE_ONLY_FOR_FIRST_ORDER' => 'Sorry, you can only use a referral before placing your first order.',
    'VALIDATION_ERROR' => 'Validation Error.',
    'INVALID_CREDENTIALS' => 'Invalid Credentials.',
    'EMAIL_NOT_VERIFIED' => 'Email not verified, verify your email first.',
    'ACCOUNT_DEACTIVATED' => 'You are deactivated, kindly contact admin.',
    'NO_RECORD' => 'No Record Found.',
    'NO_SELLER' => 'No seller found against this id.',
    'NO_STORES_FOUND' => 'No stores found in this area.',
    'ORDER_CANCELLED' => 'Cancelled.',
    'INVALID_PROMOCODE' => 'Invalid promo code.',
    'EXPIRED_PROMOCODE' => 'This promo code has been expired.',
    'MISSING_OR_INVALID_DATA' => 'Required fields missing or invalid data.',
    'VERIFICATION_FAILED' => 'You have entered a invalid verification code.',
    'ITEM_DELETED' => 'Data deleted successfully.',
    'MAX_LIMIT' => 'Promo code usage has reached its maximum limit.',
    'DATA_ALREADY_EXISTS' => 'Data already exists against id:- ',
    'CACHE_REMOVED_FAILED' => 'Sorry due to some issue your cahce can not be removed.',
    'QTY_SHOULD_NOT_BE_GREATER' => 'You cannot enter quantity more then your stock.',
    /*
    |--------------------------------------------------------------------------
    | Digital Ocean Bucket
    |--------------------------------------------------------------------------
    */
    'BUCKET' => 'https://user-imgs.sgp1.digitaloceanspaces.com/',
    /*
    |--------------------------------------------------------------------------
    | Stuart Sandbox Cridentials
    |--------------------------------------------------------------------------
    */
    'STUART_SANDBOX_CLIENT_ID' => '7faa9066d638cb94b61f18040355f59ffd124cd94b5444f1ee992d1e3e594a19',
    'STUART_SANDBOX_CLIENT_SECRET' => '9db9dac8282818a4000a75c996fbcb470f8d67835ff26ee442abf4b496ae534b',
    'STUART_SANDBOX_JOBS_URL' => 'https://api.sandbox.stuart.com/v2/jobs',
    'STUART_SANDBOX_TOKEN_URL' => 'https://api.sandbox.stuart.com/oauth/token',
    /*
    |--------------------------------------------------------------------------
    | Stuart Production Cridentials
    |--------------------------------------------------------------------------
    */
    'STUART_PRODUCTION_CLIENT_ID' => '75f7341f983c842b6a4847707a1a03d4413687e7223c3f51be34359c2fa9e505',
    'STUART_PRODUCTION_CLIENT_SECRET' => '0144e8a9978851e7005a5a3ef53cba22dc1b6102f49c7add5bb22dedf74c9ba2',
    'STUART_PRODUCTION_JOBS_URL' => 'https://api.stuart.com/v2/jobs',
    'STUART_PRODUCTION_TOKEN_URL' => 'https://api.stuart.com/oauth/token',
    /*
    |--------------------------------------------------------------------------
    | Stripe Production Cridentials
    |--------------------------------------------------------------------------
    */
    'STRIPE_LIVE_API_KEY' => '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:',
    'STRIPE_LIVE_PUBLISH_KEY' => 'pk_live_51IY9sYIiDDGv1gaVD23fTmgGwOdmPwFKvCP64BrGQPQngITPSBBOclUe6sz8vyN18Kli1iKq2JfeR754kkdbSm5T00u0M4kL0H',
    /*
    |--------------------------------------------------------------------------
    | Stripe Test Cridentials Key
    |--------------------------------------------------------------------------
    */
    'STRIPE_TEST_API_KEY' => 'sk_test_51IY9sYIiDDGv1gaVKsxU0EXr96lHcCvwXHwYAdN81Cqrj1TBL4HErJpczWJpYFIQ1qbCOQxnxIM3UfsBtWC2MKeD00QRkUKg6q:',
    'STRIPE_TEST_PUBLISH_KEY' => 'pk_test_51IY9sYIiDDGv1gaVP2DSlWvT0tnrFstp62txvamB1icDpBjwXy1KtrZmiWLFjrxHmxlMrTyWbnnBWNMblbNMizwS004MoCvzJB',
    /*
    |--------------------------------------------------------------------------
    | Admin Email
    |--------------------------------------------------------------------------
    */
    'ADMIN_EMAIL' => 'admin@teekit.co.uk',
    /*
    |--------------------------------------------------------------------------
    | Site URL
    |--------------------------------------------------------------------------
    */
    'LIVE_SITE_URL' => 'https://app.teekit.co.uk/',
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
    'HTTP_SERVICE_UNAVAILABLE' => 503,
    'HTTP_GATEWAY_TIMEOUT' => 504,
];

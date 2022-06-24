<?php

namespace App\Utilities;

class ErrorList
{
    const INVALID_CREDENTIALS = "invalid_credentials";
    const GOOGLE_INVALID_GRANT = "google_invalid_grant";
    const REGISTRATION_FAILED = "registration_failed";
    const TOKEN_EXPIRED = "token_expired";
    const NOT_FOUND = "not_found";
    const NOT_SELLER = "not_seller";
    const NOT_ADMIN = "not_admin";
    const NOT_STAFF = "not_staff";
    const NOT_CUSTOMER = "not_customer";
    const PRODUCT_CREATED_FAILED = "product_created_failed";
    const PRODUCT_UPDATE_SUCCESS = "product_update_success";
    const PRODUCT_UPDATED_FAILED = "product_updated_failed";
    const PRODUCT_DELETE_FAILED = "product_delete_failed";
    const PRODUCT_NOT_OWNED = "product_not_owned";
    const EMPTY_STOCK = "empty_stock";
    const STOCK_NOT_ENOUGH = "stock_not_enough";
    const PRODUCT_NOT_FOUND = "product_not_found";
    const SHOPPING_CREATED_FAILED = "shopping_created_failed";
    const SHOPPING_UPDATED_FAILED = "shopping_updated_failed";
    const ADDRESS_CREATED_FAILED = "address_created_failed";
    const ADDRESS_UPDATED_FAILED = "address_updated_failed";
    const ADDRESS_NOT_FOUND = "address_not_found";
    const CARTS_NOT_FOUND = "carts_not_found";
    const CARTS_IS_READY = 'carts_is_ready';
    const UPLOAD_FAILED = "upload_failed";
    const CATEGORY_CREATE_FAILED = "category_create_failed";
    const CATEGORY_UPDATE_FAILED = "category_update_failed";
    const CATEGORY_DELETE_FAILED = "category_delete_failed";
    const CATEGORY_NOT_FOUND = "category_not_found";
    const SUCCESS = "success";
    const FAILED = "failed";
    const ORDER_CREATE_FAILED = "order_create_failed";
    const ORDER_NOT_COMPLETED = "order_not_completed";
    const BANK_DISABLED = "this_bank_is_disabled";
    const COUPON_APPLIED = "coupon_has_been_applied";
    const COUPON_NOT_FOUND = "coupon_not_found";
    const COUPON_CANT_BE_USED = "coupon_can't_be_used";
    const COUPON_ALREADY_USED = "you_already_used_this_coupon!";
    const COUPON_EXPIRED = "coupon_expired";
    const BALANCE_NOT_SUFFICIENT = "the_balance_is_not_sufficient";
    const WISHLIST_NOT_FOUND = 'wishlist_not_found';
    const WISHLIST_CREATE_FAILED = 'wishlist_create_failed';
    const USER_NOT_FOUND = "user_not_found";
    const PICKUP_UNAVAILABLE = "pick_up_unavailable_for_this_courier";
    const ORDER_NOT_FOUND = 'order_not_found';
    const ORDER_DETAIL_NOT_FOUND = 'order_detail_not_found';
}

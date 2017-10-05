# Foodkit API docs #

## Build ##

To build the compiled .apib file, simply run:

```
make
```

You'll see the output:

```
rm -f build/foodkit-api.apib
cat src//_intro.apib > build/foodkit-api.apib
cat src//storefront/*.apib >> build/foodkit-api.apib
cat src//vendor/*.apib >> build/foodkit-api.apib
cat src//support/*.apib >> build/foodkit-api.apib
```

## Example ##

You can see an example of a Apiary-powered API Blueprint definition [here](http://docs.foodkitapiexample.apiary.io/).

The actual raw file is shown below for reference:

```
FORMAT: 1A
HOST: http://api.foodkit.io/

# Foodkit API Example

This is an example of API Blueprint documentation for the Foodkit API.

### Customer accounts [/api/v1/storefront/customers]

#### Create account [POST]

+ Request (application/json)

        {
            "name": "Corey Mcmahon",
            "email": "corey@ginja.co.th",
            "phone_number": "09428971234",
            "password": "password1"
        }

+ Response 200 (application/json)

        {
            "id": 24717,
            "profile_image_src": null,
            "email": "corey@ginja.co.th",
            "name": "Corey Mcmahon",
            "phone_number": "09428971234",
            "idp": "password",
            "addresses": [],
            "payment_options": null,
            "default_locale": "en",
            "user_id": 21788,
            "customer_facebook_account_id": null,
            "customer_google_account_id": null
        }

### Coupon codes [/api/v3/storefront/vendors/{vendor_id}/validate]

#### Validate coupon code [GET /api/v3/storefront/vendors/{vendor_id}/validate{?code,phone_number}]

+ Parameters
    + code: FREEFOOD (string)
    + phone_number: +66942811234 (string)

+ Response 200 (application/json)

        {
            "data": {
                "id": 648,
                "code": "FREEFOOD",
                "name": "Free Food Coupon",
                "discount_type": "absolute",
                "coupon_type": "promotion",
                "amount": 59.0,
                "reason": "For Internal Usage",
                "is_enabled": 1,
                "usages": 437,
                "max_usages": 0,
                "created_by": 336,
                "created_at": "2016-10-10 03:12:56",
                "updated_at": "2017-09-27 13:06:40",
                "valid_to": null,
                "valid_from": null,
                "deleted_at": null,
                "limit_one_per_phone_number": 0,
                "referrer_phone_number": "",
                "referee_discount_percentage": 0,
                "referrer_discount": 0,
                "vendor_id": null,
                "vendor": null
            }
        }
```

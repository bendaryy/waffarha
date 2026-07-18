# `whatsapp()->get()` — Maat support WhatsApp

Fetch Maat’s support WhatsApp number and deep links.

```php
Waffarha::whatsapp()->get(): WhatsAppContact
```

- **HTTP:** `GET {base_url}/whatsapp`
- **Returns:** [`WhatsAppContact`](data-objects.md#whatsappcontact)
- **Throws:** `WaffarhaRequestException` on 404 (not configured) / 4xx / 5xx

## Example

```php
use Maat\Waffarha\Facades\Waffarha;

$whatsapp = Waffarha::whatsapp()->get();

echo $whatsapp->phoneNumber;  // as stored, e.g. "01044660885"
echo $whatsapp->phoneDigits;  // international digits, e.g. "201044660885"
echo $whatsapp->url;          // https://wa.me/201044660885
echo $whatsapp->deepLink;     // https://api.whatsapp.com/send?phone=201044660885
```

## Response

```json
{
  "ResponseCode": "200",
  "Result": "true",
  "ResponseMsg": "WhatsApp contact retrieved successfully.",
  "whatsapp": {
    "phone_number": "01044660885",
    "phone_digits": "201044660885",
    "url": "https://wa.me/201044660885",
    "deep_link": "https://api.whatsapp.com/send?phone=201044660885"
  }
}
```

Egyptian local mobiles (`01xxxxxxxxx`) are normalised to `20…` digits for
WhatsApp links. If the number is not configured, Maat returns HTTP **404**.

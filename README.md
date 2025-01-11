Here’s the updated README file with a concise configuration section:

---

# Laravel SMS Package 📲
A Laravel package for sending SMS using **PayamResan**, **KaveNegar**, and **FarazSMS** drivers.

## Requirements ⚙️
- **PHP**: `>=7.2`
- **Laravel**: `^8.0`, `^9.0`, `^10.0`

---

## Installation 🛠️
Install the package via Composer:
```bash
composer require puzzley/sms
```

---

## Configuration ⚙️

1. **Publish the Configuration File**:
   Run the following command to publish the `sms.php` config file:
   ```bash
   php artisan vendor:publish --tag=sms-config
   ```

2. **Add Environment Variables**:
   Update your `.env` file with the necessary credentials:
   ```env
   DEFAULT_SMS_SERVICE=KaveNegar

   PAYAMRESAN_USERNAME=your-username
   PAYAMRESAN_PASSWORD=your-password
   PAYAMRESAN_SERVICE_NUMBER_DEFAULT=500024200030

   KAVENEGAR_API_KEY=your-api-key
   KAVENEGAR_SERVICE_NUMBER_DEFAULT=90004803

   FARAZSMS_USERNAME=your-username
   FARAZSMS_PASSWORD=your-password
   FARAZSMS_SERVICE_NUMBER_DEFAULT=+983000505
   ```
3. **Set Default Service**:
   The default SMS service can be set in your `.env` file using `DEFAULT_SMS_SERVICE`.

---

## Usage 🚀

The package provides a unified interface for interacting with different SMS services:

```php
use SMS;

// Sending an SMS
$smsId = SMS::send('09123456789', 'Your message here');

// Using a specific driver
$smsId = SMS::driver('FarazSMS')->send('09123456789', 'Your message here');

// Check Status
$status = SMS::driver('PayamResan')->status($smsId);

// Send Verification Code
SMS::driver('KaveNegar')->sendVerifyCode('09123456789', 'Verification message');
```

---

## Credits 👏
- **Mohammad Zare Moghadam**
- **Amir Reza Rezaei**

---

## License 📜
This package is open-sourced software licensed under the [MIT License](LICENSE).

---

This README includes essential information while keeping the configuration section concise. Let me know if further adjustments are needed! 😊

# Quick Setup - Razorpay Integration

## 🚀 Get Started in 5 Minutes

### Step 1: Sign Up (2 minutes)
1. Go to https://razorpay.com
2. Click "Sign Up"
3. Create account with email
4. Verify email

### Step 2: Get API Keys (1 minute)
1. Login to Dashboard
2. Go to **Settings → API Keys**
3. You'll see TEST mode by default (good for learning)
4. Copy your:
   - **Key ID** (starts with `rzp_test_`)
   - **Key Secret** (starts with `rzp_test_`)

### Step 3: Update Your Code (1 minute)
Open `razorpay_config.php` and update:

```php
define('RAZORPAY_KEY_ID', 'paste_your_key_id_here');
define('RAZORPAY_KEY_SECRET', 'paste_your_key_secret_here');
```

### Step 4: Test Payment (1 minute)
1. Go to your website → Add items to cart
2. Click "Proceed to Checkout"
3. Fill address details
4. Click "Pay ₹XXX with Razorpay"
5. Use test card: **4111 1111 1111 1111**
6. Any future expiry date
7. Any 3-digit CVV
8. Click Pay

### Step 5: Check Database
After successful payment:
- Check `orders` table → Your order should be there
- Check `order_items` table → Items should be saved
- Your cart should be cleared

---

## 📋 Files Overview

| File | Purpose |
|------|---------|
| `razorpay_config.php` | API Keys configuration |
| `checkout.php` | Payment page with Razorpay |
| `verify_payment.php` | Server-side payment verification |
| `order_success.php` | Success page (already exists) |

---

## 🧪 Test Cards

| Card | Number | Result |
|------|--------|--------|
| Visa | 4111 1111 1111 1111 | ✅ Success |
| Mastercard | 5555 5555 5555 4444 | ✅ Success |
| RuPay | 6522 1111 1111 1111 | ✅ Success |

Expiry: Any future date
CVV: Any 3 digits

---

## ❌ Troubleshooting

**Issue:** Payment button doesn't appear
- **Fix:** Check JavaScript console (F12) for errors
- **Fix:** Ensure you updated `razorpay_config.php`

**Issue:** "Invalid API Key" error
- **Fix:** Double-check Key ID and Key Secret are correct
- **Fix:** Make sure you're using TEST keys (start with `rzp_test_`)

**Issue:** Order not saved after payment
- **Fix:** Check database connection in `db.php`
- **Fix:** Check server error logs

---

## 📝 Important Notes

1. **Learning Mode:** You're using TEST mode - no real money
2. **Free Forever:** Test payments are free for learning
3. **No Charges:** Razorpay charges fees only for live transactions
4. **100% Safe:** Your bank details are never exposed to your code
5. **Secret Key:** NEVER share or expose Secret Key

---

## 🎓 What You Learned

- ✅ Payment gateway integration
- ✅ Secure payment processing
- ✅ Database transaction handling
- ✅ API key management
- ✅ Payment verification & signature validation
- ✅ Error handling
- ✅ Production-ready code structure

---

## 🚀 Next Steps

After learning:
1. Explore Razorpay features (webhooks, refunds, subscriptions)
2. Add email notifications
3. Implement order tracking
4. Add payment receipt generation
5. Go LIVE with real transactions

---

**Happy Learning! 🎉**

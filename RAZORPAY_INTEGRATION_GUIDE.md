# Razorpay Integration Guide for Learning

## Overview
This guide integrates **Razorpay** payment gateway into your e-commerce site. Razorpay is completely free for learning and testing in sandbox mode.

---

## Step 1: Create Razorpay Account

1. Visit [razorpay.com](https://razorpay.com)
2. Click **Sign Up** and create a free account
3. Verify your email
4. Go to **Dashboard → Settings → API Keys**
5. You'll see:
   - **Key ID** (public key) - used on frontend
   - **Key Secret** (private key) - used on backend

---

## Step 2: Update Configuration

**File:** `razorpay_config.php`

Update with your TEST keys:

```php
define('RAZORPAY_KEY_ID', 'YOUR_TEST_KEY_ID_HERE');
define('RAZORPAY_KEY_SECRET', 'YOUR_TEST_KEY_SECRET_HERE');
```

⚠️ **IMPORTANT:** 
- Use **TEST** keys for learning (starts with `rzp_test_`)
- Never hardcode keys in production - use environment variables
- Keep Key Secret secure (never expose on frontend)

---

## Step 3: Test Payment Flow

### Test Card Numbers (For Sandbox Testing):

| Card Type | Number | Expiry | CVV |
|-----------|--------|--------|-----|
| Visa | 4111 1111 1111 1111 | Any future date | Any 3 digits |
| Mastercard | 5555 5555 5555 4444 | Any future date | Any 3 digits |
| RuPay | 6522 1111 1111 1111 | Any future date | Any 3 digits |

### Test Scenarios:

1. **Successful Payment:** Use any test card above
2. **Failed Payment:** Use card ending in 0000
3. **Timeout:** Use card ending in 0002

---

## Step 4: Files Created/Modified

### New Files:
- `razorpay_config.php` - Configuration file with API keys
- `checkout.php` - Payment checkout page with Razorpay integration
- `verify_payment.php` - Payment verification endpoint
- `order_success.php` - Success page after payment

### Modified Files:
- `cart.php` - Already has link to checkout

---

## Step 5: How the Payment Flow Works

```
User adds products → Cart → Checkout Page
    ↓
User fills delivery address
    ↓
Clicks "Pay with Razorpay"
    ↓
Razorpay Checkout Modal Opens
    ↓
User enters card details (TEST cards)
    ↓
Payment processed (or failed)
    ↓
Signature verification on server (verify_payment.php)
    ↓
Order saved to database
    ↓
Redirect to success page
```

---

## Step 6: Database Tables (Auto-Created)

### orders table
```sql
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    order_id VARCHAR(100) UNIQUE,
    payment_id VARCHAR(100),
    amount DECIMAL(10, 2),
    status VARCHAR(50),           -- completed, pending, failed
    payment_status VARCHAR(50),   -- success, failed
    customer_name VARCHAR(255),
    customer_email VARCHAR(255),
    customer_phone VARCHAR(20),
    customer_address TEXT,
    customer_city VARCHAR(100),
    customer_state VARCHAR(100),
    customer_pincode VARCHAR(10),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### order_items table
```sql
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id VARCHAR(100),
    product_name VARCHAR(255),
    price DECIMAL(10, 2),
    quantity INT
);
```

**Note:** Tables are auto-created on first payment if they don't exist.

---

## Step 7: Key Features Implemented

✅ Secure payment with Razorpay hosted checkout
✅ Payment signature verification
✅ Order storage in database
✅ Cart clearing after successful payment
✅ Order history tracking
✅ Responsive design
✅ Error handling & user feedback
✅ Loading states during payment processing

---

## Step 8: Testing Checklist

### Before Going Live:
- [ ] Create Razorpay account
- [ ] Get Test API Keys (rzp_test_xxx)
- [ ] Update `razorpay_config.php`
- [ ] Test payment with card ending in 1111 (should succeed)
- [ ] Test payment with card ending in 0000 (should fail)
- [ ] Verify order data saved in database
- [ ] Check cart clears after successful payment
- [ ] Test on mobile devices

### Going to Production:
- [ ] Switch to Live API Keys (rzp_live_xxx)
- [ ] Update `razorpay_config.php`
- [ ] Update merchant name/logo in checkout page
- [ ] Use environment variables for API keys
- [ ] Enable HTTPS (required by Razorpay)
- [ ] Test with real payment (small amount)

---

## Step 9: Common Issues & Solutions

### Issue: "Invalid API Key"
**Solution:** Double-check your TEST Key ID and Secret in `razorpay_config.php`

### Issue: Payment button not working
**Solution:** Ensure Razorpay JavaScript is loaded: `<script src="https://checkout.razorpay.com/v1/checkout.js"></script>`

### Issue: "Signature verification failed"
**Solution:** Verify that your Key Secret is correct. Signature is generated using Key Secret.

### Issue: Form validation not working
**Solution:** Ensure JavaScript is enabled and all form fields are filled before clicking pay button.

---

## Step 10: Security Considerations (For Learning)

1. **Never expose Secret Key:** Always keep in `.php` files, never in JavaScript
2. **Verify Signatures:** Always verify payment signature on backend
3. **Use HTTPS:** Required in production (free with Let's Encrypt)
4. **Validate Input:** Always sanitize user input with `mysqli_real_escape_string()`
5. **Environment Variables:** Use `.env` files instead of hardcoding keys

---

## Step 11: Next Steps (Production)

When you're ready for production:

1. **Switch to Live Mode:**
   - Go to Razorpay Dashboard → Settings → API Keys
   - Generate Live keys
   - Update `razorpay_config.php`

2. **Security Hardening:**
   - Use Composer autoload for Razorpay SDK
   - Implement refund handling
   - Add webhook support for real-time updates
   - Use database transactions

3. **Integrate Razorpay SDK:**
   ```bash
   composer require razorpay/razorpay
   ```

---

## Resources

- **Razorpay Documentation:** https://razorpay.com/docs/
- **Razorpay Checkout:** https://razorpay.com/docs/payments/checkout/
- **Test Cards:** https://razorpay.com/docs/payments/payments/test-cards/
- **API Reference:** https://razorpay.com/docs/api/

---

## Support

For any issues:
1. Check Razorpay Dashboard → Logs
2. Check browser console (F12) for JavaScript errors
3. Check server error logs
4. Refer to [Razorpay Docs](https://razorpay.com/docs/)

Happy Learning! 🚀

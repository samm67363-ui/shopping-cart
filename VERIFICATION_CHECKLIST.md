# ✅ Razorpay Integration Verification Checklist

Use this checklist to verify that everything is set up correctly.

---

## 📋 Pre-Setup Checklist

- [ ] **Razorpay Account Created**
  - URL: https://razorpay.com
  - Status: Account active and verified

- [ ] **API Keys Obtained**
  - Dashboard → Settings → API Keys
  - Have TEST Key ID (rzp_test_xxx)
  - Have TEST Key Secret (rzp_test_xxx)

---

## 📝 Configuration Checklist

- [ ] **razorpay_config.php exists**
  - File path: `c:\xampp\htdocs\html\razorpay_config.php`
  - Contains: `RAZORPAY_KEY_ID` constant
  - Contains: `RAZORPAY_KEY_SECRET` constant

- [ ] **Keys are updated**
  - Opened file in editor
  - Replaced with your TEST keys
  - Saved the file
  - No spaces before/after keys

---

## 🛒 Cart & Checkout Checklist

- [ ] **cart.php updated**
  - "Proceed to Checkout" button exists
  - Links to checkout.php

- [ ] **checkout.php created/updated**
  - File exists and loads
  - Shows delivery address form
  - Shows "Pay with Razorpay" button
  - Loads Razorpay JavaScript: `https://checkout.razorpay.com/v1/checkout.js`

- [ ] **Razorpay modal appears**
  - Click "Pay with Razorpay" button
  - Modal opens (not blank, not error)
  - Shows payment options

---

## 💳 Payment Processing Checklist

- [ ] **Test payment successful**
  - Use card: 4111 1111 1111 1111
  - Enter any future expiry date
  - Enter any 3-digit CVV
  - Payment completes without error

- [ ] **Payment verification working**
  - File: verify_payment.php exists
  - No errors in browser console
  - No PHP errors in server logs

- [ ] **Order saved to database**
  - Check MySQL database
  - Table `orders` created
  - New order record exists
  - Contains order_id, payment_id, amount

- [ ] **Order items saved**
  - Check MySQL database
  - Table `order_items` created
  - Items from cart saved
  - Quantities correct

---

## 📦 Order Management Checklist

- [ ] **my_orders.php working**
  - File exists and loads
  - Shows list of orders
  - Displays order details correctly
  - Order amounts correct

- [ ] **order_details.php working**
  - Click "View Details" from my_orders.php
  - Shows complete order information
  - Shows shipping address
  - Shows order items with prices
  - Shows payment status

- [ ] **order_success.php displays**
  - After successful payment, redirects to success page
  - Shows order confirmation
  - Shows order ID
  - Shows payment confirmation

---

## 🔐 Security Checklist

- [ ] **API Keys secure**
  - Keys in PHP file only (not exposed in HTML/JS)
  - `razorpay_config.php` is PHP (not accessible from browser)
  - Secret key never sent to frontend

- [ ] **Payment verification**
  - Signature verification happens on server
  - Uses HMAC-SHA256
  - Prevents tampering

- [ ] **Data validation**
  - Form fields validated before submission
  - User input sanitized in database queries
  - No SQL injection possible

---

## 🧪 Testing Checklist

### Test Case 1: Successful Payment
- [ ] Add product to cart
- [ ] Go to checkout
- [ ] Fill all address fields
- [ ] Click "Pay with Razorpay"
- [ ] Use test card 4111 1111 1111 1111
- [ ] Complete payment
- [ ] Redirected to success page
- [ ] Order appears in my_orders.php
- [ ] Database has order record
- [ ] Cart is cleared

### Test Case 2: Failed Payment
- [ ] Add product to cart
- [ ] Go to checkout
- [ ] Click "Pay with Razorpay"
- [ ] Use any card ending with 0000
- [ ] Payment fails
- [ ] Error message shows
- [ ] Cart NOT cleared
- [ ] Can retry payment

### Test Case 3: Cancelled Payment
- [ ] Add product to cart
- [ ] Go to checkout
- [ ] Click "Pay with Razorpay"
- [ ] Close modal (click X or outside)
- [ ] Payment cancelled message shows
- [ ] Cart remains unchanged
- [ ] Can retry checkout

### Test Case 4: Order History
- [ ] After successful payment
- [ ] Go to my_orders.php
- [ ] Order appears in list
- [ ] Click "View Details"
- [ ] All order info displays correctly
- [ ] Items list correct
- [ ] Amounts correct
- [ ] Address correct

---

## 📊 Database Checklist

- [ ] **orders table exists**
  ```sql
  SELECT * FROM orders;
  ```
  - Shows at least 1 record
  - All columns populated
  - Correct data types

- [ ] **order_items table exists**
  ```sql
  SELECT * FROM order_items;
  ```
  - Shows items from orders
  - Links to correct order_id
  - Quantities correct

- [ ] **Data integrity**
  - order_id is unique
  - user_id correctly linked
  - amount matches sum of items
  - timestamps correct

---

## 🚀 Performance Checklist

- [ ] **Page loads quickly**
  - checkout.php loads in <2 seconds
  - my_orders.php loads in <2 seconds
  - order_details.php loads in <2 seconds

- [ ] **JavaScript loads**
  - Razorpay script loads from CDN
  - No console errors
  - Modal appears instantly

- [ ] **Database queries fast**
  - Orders list displays quickly
  - Order details load quickly
  - Search works smoothly

---

## 🔍 Browser Console Checklist

- [ ] **No JavaScript errors**
  - Press F12 to open console
  - Console tab shows no red errors
  - Warnings are acceptable (minor)

- [ ] **Network requests successful**
  - Network tab shows no 404 errors
  - Razorpay CDN loads
  - verify_payment.php returns success

- [ ] **Razorpay script loaded**
  - Search "checkout" in sources
  - Razorpay checkout.js should be loaded

---

## 📱 Mobile Testing Checklist

- [ ] **Mobile responsive**
  - Open on mobile/tablet
  - checkout.php displays correctly
  - my_orders.php displays correctly
  - order_details.php displays correctly

- [ ] **Touch-friendly**
  - Buttons are large enough
  - Forms are easy to fill
  - No horizontal scrolling

- [ ] **Mobile payment works**
  - Razorpay modal works on mobile
  - Payment completes successfully
  - Success page displays on mobile

---

## 🐛 Debugging Checklist

### If payment button doesn't work:
- [ ] Check browser console (F12)
- [ ] Check if Razorpay script loaded
- [ ] Verify Key ID in razorpay_config.php
- [ ] Check if form validates before payment
- [ ] Check server error logs

### If payment succeeds but order not saved:
- [ ] Check verify_payment.php exists
- [ ] Check database connection in db.php
- [ ] Check orders table created
- [ ] Check order_items table created
- [ ] Check server error logs
- [ ] Check browser console

### If page errors:
- [ ] Check PHP syntax errors
- [ ] Check if files are in correct location
- [ ] Check file permissions
- [ ] Check database connection
- [ ] Review error logs

---

## ✅ Final Verification

### Before Moving Forward:
- [ ] All configuration done
- [ ] Test payment successful
- [ ] Order saved correctly
- [ ] Can view all orders
- [ ] Can view order details
- [ ] No errors in console/logs
- [ ] Mobile works correctly
- [ ] Database queries working

### Ready for Next Steps:
- [ ] Can explain payment flow
- [ ] Understand signature verification
- [ ] Know how to switch to Live keys
- [ ] Know database structure
- [ ] Comfortable with code

---

## 🎓 Learning Verification

After setup, can you answer these?

- [ ] **How does payment verification work?**
  - Answer: HMAC-SHA256 signature verification

- [ ] **What happens after successful payment?**
  - Answer: Order saved, cart cleared, redirected to success page

- [ ] **Where are API keys stored?**
  - Answer: In razorpay_config.php (backend, not exposed)

- [ ] **How to switch to production?**
  - Answer: Update keys, enable HTTPS, test with real payment

- [ ] **What test cards can you use?**
  - Answer: 4111 1111 1111 1111, 5555 5555 5555 4444, etc.

---

## 📞 Support

### If stuck on any step:
1. Check the detailed guide: `RAZORPAY_INTEGRATION_GUIDE.md`
2. Check quick start: `SETUP_QUICK_START.md`
3. Review error logs
4. Check browser console
5. Visit Razorpay docs: https://razorpay.com/docs/

---

## ✨ You're All Set!

Once all checkboxes are done, you have a **fully functional e-commerce payment system**! 🎉

Next steps:
- Explore more Razorpay features
- Add email notifications
- Implement refunds
- Go live with real payments
- Build more features on this foundation

**Happy Learning!** 🚀

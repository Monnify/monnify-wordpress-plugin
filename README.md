# Monnify Official

**Contributors:** monnify  
**Tags:** payment gateway, monnify, e-commerce, woocommerce, nigeria  
**Requires at least:** 5.6  
**Tested up to:** 6.8  
**Stable tag:** 1.0.1  
**Requires PHP:** 7.4  
**License:** GPLv2 or later  
**License URI:** [https://www.gnu.org/licenses/gpl-2.0.html](https://www.gnu.org/licenses/gpl-2.0.html)  

Monnify Official plugin provides a seamless payment experience for your customers on your WordPress website.

## Description  
Monnify Official Payment Gateway enables seamless integration of Monnify, a reliable and secure payment gateway, into your WordPress e-commerce website. With Monnify, you can accept payments from various payment methods and provide your customers with a smooth and secure checkout experience.

### Key features include:  
- Easy setup and configuration  
- Support for multiple payment methods including card payments and bank transfers  
- Real-time transaction monitoring and reporting  
- Enhanced security features to protect your customers' data  
- Seamless integration with popular e-commerce plugins  
- Supports WooCommerce block checkout  

Whether you're running a small online store or a large e-commerce platform, Monnify Official Plugin is the ideal solution for accepting payments and managing transactions with ease.

## Installation  
1. Install the plugin via the WordPress plugin installer.  
2. Activate the plugin through the **Plugins** menu.  
3. Navigate to **WooCommerce → Settings → Payments → Monnify**.  
4. Configure your API credentials and payment methods.  

## Frequently Asked Questions  

### How do I install and configure the plugin?  
1. Install the plugin via the WordPress plugin installer.  
2. Activate the plugin through the **Plugins** menu.  
3. Navigate to the plugin settings.  
4. Select **Enable Test Mode** if you would like to make test payments.  
5. Enter your Monnify API credentials.  
6. Select the payment methods you would like to support (hold `SHIFT` to select multiple in Live Mode).  

For detailed instructions, please refer to the [Monnify documentation](https://developers.monnify.com/docs/integration-tools/plugin-libraries).  

### What payment methods does Monnify support?  
Monnify supports credit/debit card payments, USSD, Pay with Phone, Bank transfers, and more. Configure your preferred methods in the plugin settings.  

## Screenshots  
1. Plugin settings screen
   ![Screenshot 1](assets/screenshot-3.png)

2. Dashboard with real-time transaction monitoring.
   ![Screenshot 2](assets/screenshot-1.png)

3. Configuration settings for Monnify payment gateway.
   ![Screenshot 3](assets/screenshot-2.png)

## External Services  
This plugin connects to Monnify’s external payment gateway API to securely process payments made through the WooCommerce checkout page. It uses Monnify’s JavaScript SDK to initiate and manage payment transactions.  

**What data is sent and when:**  
When a customer initiates payment at checkout, the plugin loads the Monnify JavaScript SDK (`https://sdk.monnify.com/plugin/monnify.js`) and sends necessary transaction details (amount, customer name, email, transaction reference) to Monnify's API.  

**Why this data is sent:**  
To facilitate real-time payment processing via Monnify's secure infrastructure.  

**Service provider:**  
- [Monnify Terms of Service](https://monnify.com/terms.html)  
- [Monnify Privacy Policy](https://monnify.com/privacy-policy.html)  

## Changelog  

### 1.0.1  
- Added webhook enhancement to allow asynchronous order confirmation in case of network failure.  

### 1.0.0  
- Initial release of the Monnify Official Payment Gateway Plugin.  
- Added support for WooCommerce block checkout.  

## Upgrade Notice  

### 1.0.0  
Upgrade to version 1.0.0 to start accepting payments through Monnify using the WooCommerce block checkout.  

---
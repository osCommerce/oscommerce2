privacy_title = Privacy Policy

privacy_body = <h3>API Credentials</h3>

<p>The PayPal App for osCommerce Online Merchant allows store owners to automatically setup and configure the App with their PayPal API credentials without the need to enter them manually. This is performed securely by granting osCommerce access to retrieve the API credentials from the store owners PayPal account.</p>

<p>Granting osCommerce access allows the following limited information to be retrieved from the store owners PayPal account:</p>

<ul>
  <li>API Username</li>
  <li>API Password</li>
  <li>API Signature</li>
  <li>Account ID</li>
</ul>

<p>No other account information is accessed (eg, PayPal account username or password, account balance, transaction history, etc.).</p>

<p>The API Username, API Password, API Signature, and Account ID information are used to automatically configure the PayPal modules bundled in the PayPal App, including:</p>

<ul>
  <li>PayPal Payments Standard</li>
  <li>PayPal Express Checkout</li>
  <li>PayPal Payments Pro (Direct Payment)</li>
  <li>PayPal Payments Pro (Hosted Solution)</li>
  <li>Log In with PayPal</li>
</ul>

<p>The process is started by using the "Retrieve Live Credentials" and "Retrieve Sandbox Credentials" buttons displayed on the PayPal App start and credentials management pages. The store owner is securely taken to PayPal's website where they are asked to grant osCommerce access to retrieve the API credentials, and are then redirected back to their online store to continue configuration of the PayPal App. This is performed with the following steps:</p>

<ol>
  <li>The store owner clicks on "Retrieve Live Credentials" or "Retrieve Sandbox Credentials" and is securely taken to an initialization page on the osCommerce website that registers the request and immediately redirects the store owner to an on-boarding page on the PayPal website. osCommerce registers the following information in the request:
    <ul>
      <li>A uniquely generated session ID.</li>
      <li>A secret ID to match against the session ID.</li>
      <li>The URL of the store owners PayPal App (to redirect the store owner back to).</li>
      <li>The IP-Address of the store owner.</li>
    </ul>
  </li>
  <li>PayPal asks the store owner to log into their existing PayPal account or to create a new account.</li>
  <li>PayPal asks the store owner to grant osCommerce permission to retrieve their API credentials.</li>
  <li>PayPal redirects the store owner back to the initialization page on the osCommerce website.</li>
  <li>osCommerce securely retrieves and stores the following information from PayPal:
    <ul>
      <li>API Username</li>
      <li>API Password</li>
      <li>API Signature</li>
      <li>Account ID</li>
    </ul>
  </li>
  <li>The store owner is automatically redirected back to their PayPal App.</li>
  <li>The PayPal App performs a secure HTTPS call to the osCommerce website to retrieve the API credentials.</li>
  <li>The osCommerce website authenticates the secure HTTPS call, sends the API credentials, and locally discards the API credentials and PayPal App URL stored in steps 1 and 5.</li>
  <li>The PayPal App configures itself with the API credentials.</li>
</ol>

<div class="pp-panel pp-panel-warning">
  <p>The API Credentials retrieved from the store owners PayPal account are only used to configure the PayPal App. osCommerce temporarily stores the API Credentials as described in this privacy policy, and discards the API Credentials as soon as the process is over. A back-end script is also run to discard any stored information for processes that have not finalized.</p>
</div>

<div class="pp-panel pp-panel-info">
  <p>osCommerce has worked closely with PayPal to ensure the PayPal App follows strict privacy and security policies.</p>
</div>

<h3>PayPal Modules</h3>

<p>PayPal modules send store owner, online store, and customer related information to PayPal to process API transactions. These include the following modules:</p>

<ul>
  <li>PayPal Payments Standard</li>
  <li>PayPal Express Checkout</li>
  <li>PayPal Payments Pro (Direct Payment)</li>
  <li>PayPal Payments Pro (Hosted Solution)</li>
  <li>Log In with PayPal</li>
</ul>

<p>The following information is included in API calls sent to PayPal:</p>

<ul>
  <li>PayPal account information of the seller / store owner including e-mail address and API credentials.</li>
  <li>Customer shipping and billing addresses.</li>
  <li>Product information including name, price, and quantity.</li>
  <li>Shipping and tax information applicable to the order.</li>
  <li>The order total and currency.</li>
  <li>Store URLs to process, verify, and finalize PayPal transactions, including success, cancelled, and IPN URLs.</li>
  <li>E-Commerce solution identification.</li>
</ul>

<div class="pp-panel pp-panel-info">
  <p>The parameters of each transaction sent to and received from PayPal can be inspected on the PayPal App Log page.</p>
</div>

<h3>App Updates</h3>

<p>The PayPal App for osCommerce Online Merchant automatically checks the osCommerce website for updates that are available to the App. This check is performed once every 24 hours and if an update is available, a notification is shown to allow the App to download and apply the update.</p>

<p>A manual check for available updates is also performed on the PayPal App Info page.</p>

<h3>Google Hosted Libraries (jQuery and jQuery UI)</h3>

<p>If jQuery or jQuery UI are not already loaded in the Administration Tool, the PayPal App administration pages automatically load the libraries securely through Google Hosted Libraries.</p>

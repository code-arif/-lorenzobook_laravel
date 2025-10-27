<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Privacy Policy & Terms — Brixl</title>
  <style>
    :root{
      --bg:#f6f7fb;
      --card:#ffffff;
      --muted:#6b7280;
      --accent:#111827;
      --primary:#1f2937;
      --padding:18px;
      --radius:14px;
      font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
    }

    html,body{height:100%}
    body{
      margin:0;
      background:linear-gradient(180deg,var(--bg),#eef2f6);
      -webkit-font-smoothing:antialiased;
      color:var(--primary);
      display:flex;
      align-items:flex-start;
      justify-content:center;
      padding:20px;
    }

    .container{
      width:100%;
      max-width:1320px;
      min-height:90vh;
      display:flex;
      flex-direction:column;
      gap:12px;
    }

    .topbar{
      display:flex;
      align-items:center;
      gap:12px;
    }

    .back-btn{
      width:40px;
      height:40px;
      border-radius:10px;
      background:var(--card);
      display:inline-flex;
      align-items:center;
      justify-content:center;
      box-shadow:0 6px 18px rgba(16,24,40,0.06);
      border:1px solid rgba(15,23,42,0.04);
      cursor:pointer;
    }
    .back-btn svg{width:18px;height:18px;opacity:.85}

    .title{
      font-size:16px;
      font-weight:600;
      letter-spacing:0.1px;
    }

    .tabs{
      display:flex;
      gap:8px;
      margin-top:6px;
    }
    .tab{
      flex:1;
      text-align:center;
      padding:10px;
      border-radius:12px;
      background:transparent;
      border:1px solid transparent;
      font-weight:600;
      cursor:pointer;
    }
    .tab.active{
      background:var(--card);
      border-color:rgba(15,23,42,0.06);
      box-shadow:0 8px 20px rgba(16,24,40,0.06);
    }

    .card{
      background:var(--card);
      border-radius:var(--radius);
      padding:var(--padding);
      box-shadow:0 10px 30px rgba(16,24,40,0.05);
      border:1px solid rgba(15,23,42,0.04);
      overflow:hidden;
      display:flex;
      flex-direction:column;
      gap:12px;
      min-height:80vh;
    }

    .actions{
      display:flex;
      gap:8px;
      justify-content:flex-end;
    }
    .btn{
      padding:8px 12px;
      border-radius:10px;
      border:1px solid rgba(15,23,42,0.06);
      background:transparent;
      cursor:pointer;
      font-weight:600;
    }
    .btn.primary{
      background:#0f172a;
      color:white;
      border-color:transparent;
    }

    .content{
      overflow:auto;
      max-height:56vh;
      padding-right:6px;
    }
    .content h2{
      margin:6px 0 10px;
      font-size:14px;
    }
    .content p{
      margin:8px 0;
      line-height:1.55;
      color:var(--muted);
      font-size:13px;
    }
    .content ul{padding-left:18px;color:var(--muted)}
    .small{font-size:12px;color:var(--muted)}

    /* print-friendly */
    @media print{
      body{background:white;padding:0}
      .container{box-shadow:none;max-width:100%;padding:10px}
      .topbar,.tabs,.actions{display:none}
      .card{box-shadow:none;border-radius:0;padding:0}
    }
  </style>
</head>
<body>
  <div class="container" role="main" aria-labelledby="pageTitle">
    <div class="topbar">
      <button class="back-btn" onclick="history.back()" aria-label="Back">
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
          <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>
      <div>
        <div id="pageTitle" class="title">Policies</div>
        <div class="small">Privacy Policy & Terms and Conditions</div>
      </div>
    </div>

    <div class="tabs" role="tablist" aria-label="Policy tabs">
      <button id="tab-privacy" class="tab active" role="tab" aria-selected="true" onclick="showTab('privacy')">Privacy Policy</button>
      <button id="tab-terms" class="tab" role="tab" aria-selected="false" onclick="showTab('terms')">Terms & Conditions</button>
    </div>

    <div class="card" id="policyCard">
      <div style="display:flex;align-items:center;justify-content:space-between">
        <div style="font-weight:700">Policy Details</div>
        <div class="actions">
          <button class="btn" onclick="printPolicy()">Print</button>
          <button class="btn primary" id="downloadBtn">Download PDF</button>
        </div>
      </div>

      <div class="content" id="content">
        <!-- Privacy Policy (default) -->
        <section id="privacy" aria-labelledby="privacyTitle">
          <h2 id="privacyTitle">Privacy Policy</h2>
          <p><strong>Effective Date:</strong> [Insert Date]</p>

          <p>Brixl ("we", "us", or "our") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our application.</p>

          <h3>1. Information We Collect</h3>
          <p>We may collect the following types of information:</p>
          <ul>
            <li><strong>Personal Information:</strong> name, email address, phone number, and other information you provide when signing up or contacting support.</li>
            <li><strong>Usage Data:</strong> device information, IP address, analytics, and app interactions to improve our service.</li>
          </ul>

          <h3>2. How We Use Information</h3>
          <p>We use collected information to:</p>
          <ul>
            <li>Provide, operate, and maintain our services.</li>
            <li>Respond to user inquiries and support requests.</li>
            <li>Process payments and manage subscriptions via third-party payment processors.</li>
            <li>Send you updates, security alerts, and marketing (where you have consented).</li>
          </ul>

          <h3>3. Data Sharing</h3>
          <p>We do not sell your personal information. We may share information with service providers who assist in operating the app (e.g., hosting, analytics, payment providers). We may also disclose information to comply with legal obligations.</p>

          <h3>4. Security</h3>
          <p>We take reasonable steps to protect personal information using encryption, secure servers, and access controls. However, no method of transmission over the internet or electronic storage is 100% secure.</p>

          <h3>5. Your Rights</h3>
          <p>You may request access to, correction of, or deletion of your personal data. You can also opt out of marketing messages by contacting us.</p>

          <h3>6. Children's Privacy</h3>
          <p>We do not knowingly collect personal information from children under 13. If we learn we have collected such data, we will take steps to delete it.</p>

          <h3>7. Contact</h3>
          <p>If you have questions about this Privacy Policy, contact us at <a href="mailto:privacy@brixlapp.com">privacy@brixlapp.com</a>.</p>

          <p class="small">Last updated: [Insert Date]</p>
        </section>

        <!-- Terms & Conditions (hidden by default) -->
        <section id="terms" aria-labelledby="termsTitle" style="display:none">
          <h2 id="termsTitle">Terms & Conditions</h2>
          <p><strong>Effective Date:</strong> [Insert Date]</p>

          <p>Welcome to Brixl. These Terms and Conditions ("Terms") govern your access to and use of our mobile application and services. By using our services you agree to these Terms.</p>

          <h3>1. Acceptance</h3>
          <p>By accessing or using the app you accept and agree to be bound by these Terms. If you do not agree, do not use the app.</p>

          <h3>2. Eligibility</h3>
          <p>You must be at least 13 years old to use our app. If under 18, you should have parental or guardian consent.</p>

          <h3>3. Subscriptions & Payments</h3>
          <p>Subscription plans are billed according to the plan selected. Payments are processed by third-party payment processors. Refunds are handled per our refund policy and the terms of the payment processor.</p>

          <h3>4. User Conduct</h3>
          <p>You agree not to misuse the service, attempt to gain unauthorized access to the app or servers, or transmit harmful code.</p>

          <h3>5. Intellectual Property</h3>
          <p>All content, trademarks, and materials within the app are owned or licensed by Brixl. You may not use these materials without permission.</p>

          <h3>6. Termination</h3>
          <p>We may suspend or terminate access for users who violate these Terms or for other business reasons. You can cancel your subscription at any time via account settings.</p>

          <h3>7. Limitation of Liability</h3>
          <p>To the fullest extent permitted by law, Brixl is not liable for indirect, incidental, special, or consequential damages arising out of your use of the app.</p>

          <h3>8. Changes to Terms</h3>
          <p>We may update these Terms from time to time. Continued use after changes means you accept the new Terms.</p>

          <h3>9. Contact</h3>
          <p>For questions about these Terms, contact us at <a href="mailto:support@brixlapp.com">support@brixlapp.com</a>.</p>

          <p class="small">Last updated: [Insert Date]</p>
        </section>
      </div>
    </div>
  </div>

  <script>
    function showTab(name){
      document.getElementById('privacy').style.display = name === 'privacy' ? '' : 'none';
      document.getElementById('terms').style.display   = name === 'terms' ? '' : 'none';

      const t1 = document.getElementById('tab-privacy');
      const t2 = document.getElementById('tab-terms');
      if(name === 'privacy'){
        t1.classList.add('active'); t1.setAttribute('aria-selected','true');
        t2.classList.remove('active'); t2.setAttribute('aria-selected','false');
      } else {
        t2.classList.add('active'); t2.setAttribute('aria-selected','true');
        t1.classList.remove('active'); t1.setAttribute('aria-selected','false');
      }
    }

    function printPolicy(){
      window.print();
    }

    // Simple "Download PDF" helper using print-to-PDF flow; open print dialog
    document.getElementById('downloadBtn').addEventListener('click', function(){
      // On mobile/desktop, this will open the print dialog from which user can "Save as PDF".
      printPolicy();
    });
  </script>
</body>
</html>

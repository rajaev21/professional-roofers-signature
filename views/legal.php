<?php
include("../backend/nodes.php");
include("../components/system/config.php");

$pageTitle = "Legal";
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <?php include("../components/system/head.php") ?>
</head>

<body>
  <?php include("./components/side-bar.php") ?>
  <div class="wrapper d-flex flex-column min-vh-100">
    <?php include("./components/header.php") ?>

    <div class="body flex-grow-1">
      <div class="container-lg px-4 mb-3">
        <div class="row mb-2">
          <div class="col-6 col-md-6">
            <h1 class="h3"><?= $pageTitle ?></h1>
          </div>
          <div class="col-6 col-md-6 text-end">
          </div>
        </div>

        <div class="card">
          <div class="card-body">
            <ul class="nav nav-tabs mb-3" id="legalTabs" role="tablist">
              <li class="nav-item" role="presentation">
                <button class="nav-link active" id="terms-tab" data-bs-toggle="tab" data-bs-target="#terms" type="button" role="tab" aria-controls="terms" aria-selected="true">
                  Terms & Conditions
                </button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="privacy-tab" data-bs-toggle="tab" data-bs-target="#privacy" type="button" role="tab" aria-controls="privacy" aria-selected="false">
                  Privacy Policy
                </button>
              </li>
            </ul>
            <div class="tab-content mb-3" id="legalTabsContent">
              <div class="tab-pane fade show active" id="terms" role="tabpanel" aria-labelledby="terms-tab">
                <div class="container">
                  <h5 class="card-title mb-3">
                    Terms and Conditions for Electronic Signature Use
                  </h5>

                  <p style="text-indent: 50px;">
                    By accessing and using our services, you agree to these Terms and Conditions for Electronic Signature Use ("Terms"). These Terms govern the use of electronic signatures for transactions and communications between you ("User") and [Professional Roofers Signatures]. Please read these Terms carefully.
                  </p>
                  <p style="text-indent: 50px;">
                    By using electronic signatures in your interactions with [Professional Roofers Signatures], you acknowledge and agree to the terms outlined below.
                  </p>

                  <ol>
                    <li class="fw-bold py-2">Legal Acceptance of Electronic Signatures</li>
                    <p>
                      Under the <strong>Electronic Signatures in Global and National Commerce Act (ESIGN Act, 15 U.S.C. § 7001 et seq.)</strong> and the <strong>Uniform Electronic Transactions Act (UETA),</strong> electronic signatures and records have the same legal force and effect as handwritten signatures and paper documents in the United States. By agreeing to these Terms, you acknowledge that:
                    </p>
                    <ul>
                      <li>An electronic signature is legally binding;</li>
                      <li>Electronically signed agreements or contracts are enforceable and carry the same legal weight as physical documents;</li>
                      <li>You consent to the use of electronic signatures and records for all interactions with [Professional Roofers Signatures].</li>
                    </ul>
                    <li class="fw-bold py-2">Consent to Electronic Transactions</li>
                    <p>
                      By using our services, you agree to conduct business electronically and receive all relevant disclosures, notices, contracts, and other communications in electronic format. You understand and agree that:
                    </p>
                    <ul>
                      <li>You can retain electronic records by printing or saving them electronically for your records;</li>
                      <li>You have the right to request paper copies of electronic documents by contacting [Professional Roofers Signatures], though additional fees may apply;</li>
                      <li>You may need specific hardware and software to access and retain electronic records (as specified in Section 5).</li>
                    </ul>
                    <li class="fw-bold py-2">Right to Withdraw Consent</li>
                    <p>
                      You have the right to withdraw your consent to use electronic signatures or receive electronic communications. To do so, you must provide written notice to [Professional Roofers Signatures] at [Company Contact Information]. Withdrawal of consent may impact your ability to use certain services, and [Professional Roofers Signatures] reserves the right to terminate or limit services upon withdrawal.
                    </p>
                    <li class="fw-bold py-2">Technology Requirements</li>
                    <p>
                      To access, sign, and retain electronic documents, you must have:
                    </p>
                    <ul>
                      <li>A computer, mobile device, or similar device capable of accessing the internet;</li>
                      <li>Software to view PDF files or other commonly used formats (e.g., Adobe Reader);</li>
                      <li>A valid email account for receiving notifications and communications.</li>
                    </ul>
                    <p>
                      You are responsible for ensuring that you have the necessary equipment and software to access and retain electronic records.
                    </p>
                    <li class="fw-bold py-2">Verification of Identity</li>
                    <p>
                      By using electronic signatures, you affirm that you are authorized to sign and that the information provided is accurate and truthful. [Professional Roofers Signatures] reserves the right to require additional verification of identity for any electronically signed document.
                    </p>
                    <li class="fw-bold py-2">Governing Law</li>
                    <p>
                      These Terms are governed by all applicable laws in the United States, including the <strong>ESIGN Act, UETA,</strong> and relevant state laws. Any disputes arising from or related to the use of electronic signatures will be governed by the laws of [State Name], unless otherwise specified.
                    </p>
                    <li class="fw-bold py-2">Modification of Terms</li>
                    <p>
                      [Professional Roofers Signatures] reserves the right to modify or update these Terms at any time without prior notice. Continued use of electronic signatures or services provided by [Professional Roofers Signatures] after such modifications constitutes acceptance of the revised Terms.
                    </p>
                    <li class="fw-bold py-2">Severability</li>
                    <p>
                      If any provision of these Terms is deemed invalid or unenforceable under applicable law, the remaining provisions will remain valid and enforceable to the fullest extent permitted by law.
                    </p>
                    <li class="fw-bold py-2">Acceptance of Terms</li>
                    <p>
                      By clicking "I Agree," submitting an electronic form, or otherwise indicating your acceptance electronically, you confirm that:
                    </p>
                    <ul>
                      <li>You have read and understood these Terms and Conditions;</li>
                      <li>You consent to the use of electronic signatures for transactions with [Professional Roofers Signatures];</li>
                      <li>You agree that electronic signatures are legally binding and enforceable.</li>
                    </ul>
                  </ol>
                </div>
              </div>
              <div class="tab-pane fade" id="privacy" role="tabpanel" aria-labelledby="privacy-tab">
                <div class="container">
                  <h5 class="card-title mb-3">
                    Privacy Policy
                  </h5>

                  <p>
                    <strong>Effective Date:</strong> September 01, 2025
                    <br>
                    <strong>Last Updated:</strong> September 01, 2025
                  </p>

                  <ol>
                    <li class="fw-bold py-2">Introduction</li>
                    <p>
                      Welcome to <strong>Professional Roofers Signatures</strong> (“we,” “our,” “us”).
                    </p>
                    <p>
                      We value your privacy and are committed to protecting your personal information. This Privacy Policy explains how we collect, use, disclose, and safeguard your data when you use our website, mobile application, and related services (collectively, the “Platform”).
                    </p>
                    <p>
                      By using Professional Roofers Signatures, you agree to the practices described in this Privacy Policy. If you do not agree, please discontinue use of the Platform.
                    </p>
                    <hr>
                    <li class="fw-bold py-2">Information We Collect</li>
                    <ul style="list-style-type: none" class="p-0">
                      <li class="fw-bold">2.1 Information You Provide</li>
                      <p class="my-2">
                        We collect the following information directly from you when you use our Platform:
                      </p>
                      <ul style="list-style-type: disc;" class="mb-3">
                        <li class="my-2">
                          <strong>Account Information:</strong>
                          Name, email address, phone number, company name, and password.
                        </li>
                        <li class="my-2">
                          <strong>Signature Data: </strong>
                          Your electronic signature, initials, and any uploaded images or drawings used for signing.
                        </li>
                        <li class="my-2">
                          <strong>Documents: </strong>
                          Any files, forms, or agreements you upload, send, or sign using our system.
                        </li>
                        <li class="my-2">
                          <strong>Payment Information:</strong>
                          Credit card details or other billing information processed via our secure third-party payment processors.
                        </li>
                        <li class="my-2">
                          <strong>Support Communications: </strong>
                          When you contact customer support, we collect any information you provide in your message.
                        </li>
                      </ul>
                      <li class="fw-bold">2.2 Automatically Collected Information</li>
                      <p class="my-2">
                        When you access Professional Roofers Signatures, we automatically collect:
                      </p>
                      <ul style="list-style-type: disc;" class="mb-3">
                        <li class="my-2">
                          <strong>Log Data: </strong>
                          IP address, browser type, operating system, device identifiers, access times, and pages viewed.
                        </li>
                        <li class="my-2">
                          <strong>Usage Data: </strong>
                          Interactions within the Platform (e.g., documents viewed, actions performed).
                        </li>
                        <li class="my-2">
                          <strong>Cookies & Tracking: </strong>
                          We use cookies and similar technologies to improve user experience and analytics.
                        </li>
                      </ul>
                    </ul>
                    <hr>
                    <li class="fw-bold py-2">How We Use Your Information</li>
                    <p class="my-2">
                      We use your personal information to:
                    </p>
                    <ol class="mb-3">
                      <li class="my-2 ps-2">Provide and maintain the Professional Roofers Signatures service.</li>
                      <li class="my-2 ps-2">Facilitate secure electronic signature and document workflows.</li>
                      <li class="my-2 ps-2">Verify user identity and authenticate access.</li>
                      <li class="my-2 ps-2">Process payments and manage subscriptions.</li>
                      <li class="my-2 ps-2">Communicate with you regarding your account, updates, and support.</li>
                      <li class="my-2 ps-2">Improve, personalize, and optimize the Platform.</li>
                      <li class="my-2 ps-2">Ensure compliance with applicable laws and prevent fraudulent or unauthorized activities.</li>
                    </ol>
                    <hr>
                    <li class="fw-bold py-2">Legal Basis for Processing (for EEA/UK Users)</li>
                    <p class="my-2">
                      If you are located in the European Economic Area or the UK, we process your data based on one or more of the following legal grounds:
                    </p>
                    <ul style="list-style-type: disc;" class="mb-3">
                      <li class="my-2"><strong>Contractual necessity:</strong> To provide services you request.</li>
                      <li class="my-2"><strong>Legitimate interests:</strong> To enhance platform performance and ensure security.</li>
                      <li class="my-2"><strong>Consent:</strong> For specific processing activities like marketing.</li>
                      <li class="my-2"><strong>Legal obligation:</strong> To comply with applicable laws.</li>
                    </ul>
                    <hr>
                    <li class="fw-bold py-2">How We Share Your Information</li>
                    <p class="my-2">
                      We do <strong>not sell or rent</strong> your personal data. We may share information with:
                    </p>
                    <ul style="list-style-type: disc;" class="mb-3">
                      <li class="my-2"><strong>Service Providers:</strong> Trusted vendors who assist in hosting, payments, email delivery, analytics, or customer support.</li>
                      <li class="my-2"><strong>Legal Authorities:</strong> When required by law, regulation, or valid legal request.</li>
                      <li class="my-2"><strong>Business Transfers:</strong> In the event of a merger, acquisition, or sale of assets, your data may be transferred as part of that transaction.</li>
                    </ul>
                    <p>All third parties are contractually bound to protect your information and use it only for the purposes outlined by Professional Roofers Signatures.</p>
                    <hr>
                    <li class="fw-bold py-2">Data Retention</li>
                    <p>
                      We retain your personal and document data only as long as necessary to fulfill the purposes outlined in this policy, or as required by law.
                      You may request deletion of your account or documents by contacting us at [Insert Contact Email].

                    </p>
                    <hr>
                    <li class="fw-bold py-2">Data Security</li>
                    <p class="my-2">
                      We implement robust security measures including:
                    </p>
                    <ul style="list-style-type: disc;" class="mb-3">
                      <li class="my-2"><strong>Encryption:</strong> All data is encrypted in transit (SSL/TLS) and at rest.</li>
                      <li class="my-2"><strong>Access Controls:</strong> Role-based permissions and authentication protocols.</li>
                      <li class="my-2"><strong>Regular Audits:</strong> System monitoring and security reviews to prevent unauthorized access or data loss.</li>
                    </ul>
                    <p>While we take every reasonable step to protect your data, no system is completely secure. You are responsible for maintaining the confidentiality of your account credentials.</p>
                    <hr>
                    <li class="fw-bold py-2">Your Rights and Choices</li>
                    <p class="my-2">
                      Depending on your jurisdiction, you may have the following rights:
                    </p>
                    <ul style="list-style-type: disc;" class="mb-3">
                      <li class="my-2">Access the personal data we hold about you.</li>
                      <li class="my-2">Request correction or deletion of your data.</li>
                      <li class="my-2">Withdraw consent at any time (where applicable).</li>
                      <li class="my-2">Request restriction or objection to processing.</li>
                      <li class="my-2">Data portability.</li>
                    </ul>
                    <p>To exercise your rights, contact <strong>privacy@app.primesealsignatures.com</strong>.</p>
                    <hr>
                    <li class="fw-bold py-2">Cookies and Tracking Technologies</li>
                    <p>
                      We use cookies and similar tools for authentication, analytics, and user experience.
                      You can control cookies through your browser settings. However, disabling cookies may affect functionality.
                    </p>
                    <hr>
                    <li class="fw-bold py-2">International Data Transfers</li>
                    <p>
                      If you access our services outside the country where our servers are located, your data may be transferred internationally.
                      We ensure appropriate safeguards (such as Standard Contractual Clauses) for such transfers.
                    </p>
                    <hr>
                    <li class="fw-bold py-2">Third-Party Links</li>
                    <p>
                      Our Platform may contain links to third-party websites.
                      We are not responsible for the privacy practices or content of these external sites. Please review their privacy policies separately.
                    </p>
                    <hr>
                    <li class="fw-bold py-2">Children's Privacy</li>
                    <p>
                      Professional Roofers Signatures is not intended for individuals under <strong>18 years of age</strong>.
                      We do not knowingly collect personal information from minors. If we learn that we have, we will delete it promptly
                    </p>
                    <hr>
                    <li class="fw-bold py-2">Changes to This Policy</li>
                    <p>
                      We may update this Privacy Policy from time to time to reflect legal or operational changes.
                      The latest version will always be posted on our website with the updated “Effective Date.”
                    </p>
                    <hr>
                    <li class="fw-bold py-2">Contact Us</li>
                    <p>
                      If you have questions, concerns, or complaints about this Privacy Policy or our data practices, contact us at: <br>
                      <strong>Professional Roofers Signatures Privacy Office</strong> <br>
                      Email: <strong>admin@app.primesealsignatures.com</strong><br>
                      Website: <strong>www.app.primesealsignatures.com</strong><br>
                      Address: 25 Eastern Steel Rd Milford CT USA
                    </p>
                  </ol>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <?php include("./components/footer.php") ?>
  </div>

</body>
<?php include("../components/system/scripts.php") ?>

</html>
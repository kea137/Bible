# Security Policy

Thank you for taking the time to help keep kea137/Bible secure. This document explains how to report security vulnerabilities and what to expect once a report is submitted.

## Supported Versions
We support the latest release and the previous minor release. If you are unsure whether your version is supported, report the issue and include the exact version/commit SHA; we'll confirm support status.

## Reporting a Vulnerability (preferred)
Preferred options (in order):
1. GitHub Security Advisories (private): https://github.com/kea137/Bible/security/advisories
2. Email: security@your-domain.example (replace with the project's security contact)
3. If you cannot use the above, open a private issue and mark it clearly as a security report.

Do NOT open a public issue for security-sensitive reports.

When reporting, please include the information below (a minimal reproducible example is very helpful):

- Title: short summary
- Affected component(s) and version(s)
- Vulnerability type (e.g., XSS, SQLi, remote code execution)
- Detailed description and impact (what an attacker can achieve)
- Steps to reproduce (PoC or proof-of-concept code if available)
- Expected and actual behavior
- Logs, stack traces, or screenshots (if applicable)
- Your contact information (email/PGP key) and whether you wish to remain anonymous
- Whether you can provide or test a fix/patch

Example template:
- Summary:
- Affected versions:
- Severity (your assessment):
- Steps to reproduce:
- PoC (if available):
- Mitigation/workaround (if any):
- Contact:

## Response Policy and Timeline
We take security reports seriously and aim to respond as follows:

- Acknowledgement: within 3 business days after receipt.
- Initial triage & classification: within 7 business days.
- Fix timelines (once issue is verified and reproducible):
  - Critical: patch and release within 7 days (or sooner).
  - High: patch and release within 14 days.
  - Medium: patch and release within 30 days.
  - Low: patch and release within 90 days or scheduled for the next maintenance release.

These are target timelines and may vary depending on complexity, availability of maintainers, and coordination with third parties. We'll keep you updated on progress.

## Coordination, Disclosure & CVE
- We prefer coordinated disclosure. When possible, we'll work with you to fix issues before public disclosure.
- If you need a CVE, request it during coordination and we will assist in obtaining one.
- If you prefer the issue to be disclosed publicly immediately, please state that explicitly in your report.

## Credit and Recognition
- With your permission, we will credit contributors who responsibly disclose vulnerabilities in release notes and the SECURITY.md or an ACKNOWLEDGEMENTS file.
- If you prefer to remain anonymous, tell us in your report and we will respect that.

## Safe Harbor
Please avoid exploiting vulnerabilities against production systems or accessing user data beyond what's necessary to demonstrate the issue. Do not use the vulnerability for malicious purposes. We will not pursue legal action against security researchers acting in good faith to report vulnerabilities following this policy.

## If the Vulnerability Is Not Security-Related
If your issue is not a security concern (bugs, feature requests, general support), please open a normal issue: https://github.com/kea137/Bible/issues

## Contact & Escalation
- Preferred: GitHub Security Advisories for this repository.
- Alternative: security@your-domain.example (replace with a real contact)
- Maintainer: @kea137 (GitHub account)

If you do not receive an acknowledgement within 3 business days, please re-send or escalate via a different channel (e.g., add a private comment to the advisory or send a follow-up email).

## Legal
Nothing in this policy constitutes a waiver of any rights. This policy is provided as guidance for secure reporting and does not create any legal obligations beyond applicable law.

## Changes to This Policy
We may update this policy as needed. The current version is at: SECURITY.md in this repository.

Thank you for helping to keep kea137/Bible secure.

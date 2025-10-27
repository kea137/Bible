# Contributing to kea137/Bible

Thanks for your interest in contributing! This document describes the preferred workflow for contributing to this project, how to report bugs, propose features, and submit changes. Please replace kea137/Bible and any placeholder commands below with values specific to your project.

## Table of contents
- Code of Conduct
- Getting started / Development setup
- Reporting bugs
- Proposing features
- Pull request process
- Commit message guidelines
- Branching strategy
- Tests & CI
- Style and linting
- Adding dependencies
- Security
- License & legal

---

## Code of Conduct
This project follows a Code of Conduct. Please read and follow it: [link to CODE_OF_CONDUCT.md]. Be respectful and constructive in all interactions.

---

## Getting started (development setup)
1. Fork the repository and clone your fork:
   ```bash
   git clone https://github.com/<your-username>/<repo>.git
   cd <repo>
   ```
2. Add upstream remote:
   ```bash
   git remote add upstream https://github.com/<org-or-owner>/<repo>.git
   ```
3. Install dependencies (example - replace with your stack):
   - Node:
     ```bash
     npm install
     ```
   - Python:
     ```bash
     python -m venv .venv
     source .venv/bin/activate
     pip install -r requirements.txt
     ```
4. Run tests:
   ```bash
   npm test
   # or
   pytest
   ```

Update the commands above to match this project's language and tooling.

---

## Reporting bugs
Please open an issue with the following:
- A descriptive title.
- Steps to reproduce, including minimal reproduction if possible.
- Expected behavior and actual behavior.
- Environment (OS, versions, relevant configuration).
- Any logs, stack traces, or screenshots.

Label bug reports with `bug` and `needs-triage`.

---

## Proposing features
Open an issue titled “Feature: <short description>” with:
- Motivation — why this would help.
- A short proposal or possible API/UX design.
- Any non-blocking ideas for an implementation.

Label feature requests with `enhancement` or `feature-request`.

---

## Pull request process
1. Sync your fork and create a branch:
   ```bash
   git fetch upstream
   git checkout -b feat/<short-description>
   ```
2. Make small, focused commits. Rebase frequently onto `main` (or repo default).
3. Ensure all tests pass and linters are satisfied.
4. Open a pull request against the project's `main` branch and include:
   - A clear description of the change.
   - Link to related issues.
   - Any migration notes or backward-incompatibility warnings.
   - Screenshots or logs if applicable.

We use code review to maintain quality; expect constructive feedback and be prepared to update your PR.

---

## Commit message guidelines
Follow Conventional Commits style for clear history. Examples:
- feat(parser): add support for new syntax
- fix(cli): handle empty input gracefully
- docs(readme): clarify configuration options
- chore(deps): bump dependency xyz to ^1.2.3

Keep messages concise and reference issues when appropriate, e.g., `fix: handle null values (#123)`.

---

## Branching strategy
- `main` (or `master`): production-ready code.
- Feature branches: `feat/<short-desc>` or `fix/<short-desc>`.
- Hotfix branches: `hotfix/<short-desc>` for urgent fixes.

We prefer small PRs focused on a single change.

---

## Tests & Continuous Integration
- Add tests for new functionality and bug fixes.
- Run the test suite locally before opening a PR.
- CI will run tests and linters automatically; don't merge if CI is failing.
- If you add code that requires additional CI resources, mention it in the PR description.

---

## Style and linting
Follow the existing style conventions in the project. If the project uses linters/formatters, run them before committing:
```bash
npm run lint
npm run format
# or
black .
flake8
```
Add formatting/linting changes separately from functional changes where reasonable.

---

## Adding a new dependency
- Keep dependencies minimal and well-maintained.
- Explain why the dependency is necessary in your PR.
- Verify license compatibility.
- If adding a build/runtime dependency, update documentation and CI if required.

---

## Security
For security vulnerabilities, do NOT open a public issue. Use GitHub's security advisories or contact the maintainers privately at: [security@your-org.example] (replace with the project's security contact). Follow the project's security disclosure policy.

---

## Documentation
- Update README.md, docs/, and any user-facing docs when behavior or public APIs change.
- Document configuration options, environment variables, and examples.

---

## PR checklist (maintainers & contributors)
Before submitting, ensure:
- [ ] The change is covered by tests where applicable
- [ ] All tests pass locally
- [ ] Linting/formatting applied
- [ ] Relevant docs updated
- [ ] Commit messages follow conventions
- [ ] PR description explains why the change is needed

---

## Releases
We follow semantic versioning. For maintainers: bump the version appropriately for breaking changes, features, and fixes and update changelog/release notes.

---

## Questions
If you're unsure how to proceed, ask in an issue and tag maintainers or open a draft PR for discussion.

---

Thank you for contributing — your help makes this project better!

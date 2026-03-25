# Guard-Aware Email Verification

## Overview

The infrastructure implements a guard-aware email verification flow for all supported stakeholder types.

Supported verification contexts:

- `web`
- `student`
- `employer`

## Behaviour

Email verification links are generated according to the stakeholder guard associated with the notifiable model.

Verification handling is route-based rather than session-dependent. This allows signed verification links to function correctly even when the user is not currently authenticated.

## Key Properties

- guard-aware verification link generation
- route-based guard resolution
- correct provider/model lookup per guard
- safe handling of already-verified users
- recovery flow for expired or invalid signed links

## Rationale

This design prevents cross-guard ambiguity and avoids verification failures caused by missing active sessions.

It also improves user experience by directing expired or invalid verification attempts into a recovery-oriented authentication flow rather than a generic authorisation failure path.
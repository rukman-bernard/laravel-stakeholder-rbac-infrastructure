# Research Context

This document describes the research context and scholarly lineage from which the **Laravel Stakeholder RBAC Infrastructure Artefact** was derived.

This repository publishes a reusable Laravel infrastructure artefact developed as part of a broader research programme exploring the design and implementation of **UK-aligned student information systems (SIS)** and the supporting security infrastructure required for multi-stakeholder applications.

The artefact emerges from three related studies addressing different layers of the problem space:

- domain and regulatory understanding  
- system architecture and prototype design  
- security infrastructure implementation  

Together these studies form the conceptual and technical foundation from which this reusable infrastructure artefact was extracted.

---

# Domain and Regulatory Foundation

The study **"United Kingdom Higher Education Levels, Awards, and Regulatory Oversight for Transnational Delivery with Sri Lankan Partners"** synthesises the regulatory frameworks, qualification structures, and governance models that shape UK higher-education delivery.

The review examines:

- the **Framework for Higher Education Qualifications (FHEQ)**
- the **Scottish Credit and Qualifications Framework (SCQF)**
- **exit-award pathways**
- the role of regulatory bodies such as the **Office for Students (OfS)**
- governance expectations for **transnational education (TNE)** partnerships involving Sri Lankan institutions.

This work provides the **domain knowledge and regulatory context** required to design academically credible and regulatorily aligned higher-education systems.

### Citation

Bernard, R. (2025).  
*United Kingdom Higher Education Levels, Awards, and Regulatory Oversight for Transnational Delivery with Sri Lankan Partners*.  
Zenodo.  
https://doi.org/10.5281/zenodo.17738909

---

# System Design Prototype

The design-science study **"Designing a UK-Aligned Student Information System: A Design-Science Prototype for a Hypothetical Institute (NKA)"** proposes a prototype architecture for a student information system aligned with UK academic frameworks and governance expectations.

The study demonstrates how academic structures such as:

- programmes  
- levels  
- modules  
- exit awards  
- programme configurations  

can be represented within a Laravel-based application architecture.

The prototype illustrates how UK qualification frameworks and governance expectations can be translated into a structured information system capable of supporting academic administration and programme delivery.

### Citation

Bernard, R. (2025).  
*Designing a UK-Aligned Student Information System: A Design-Science Prototype for a Hypothetical Institute (NKA).*  
Zenodo.  
https://doi.org/10.5281/zenodo.17075060

---

# Security Infrastructure Pattern

The study **"Practical Multi-Guard RBAC in Laravel 11 with Spatie Permissions and Livewire"** presents a practical implementation pattern for **multi-guard role-based access control (RBAC)** in Laravel applications.

The work demonstrates how guard-scoped roles and permissions can be implemented using **Spatie Permissions** and **Livewire**, enabling:

- clear separation of authentication contexts  
- least-privilege access control  
- guard-aware authorisation enforcement  
- integration of RBAC across middleware, policies, and UI components  

This study forms the **technical security infrastructure foundation** for the reusable artefact published in this repository.

### Citation

Bernard, R. (2025).  
*Practical Multi-Guard RBAC in Laravel 11 with Spatie Permissions and Livewire.*  
Zenodo.  
https://doi.org/10.5281/zenodo.17127207

---

# Position of This Repository

The present repository extracts and generalises the **reusable infrastructure layer** developed during the above research.

The focus of the artefact is intentionally limited to **authentication and access-control infrastructure**, rather than full domain-specific system functionality.

The repository therefore concentrates on:

- multi-guard authentication architecture  
- guard-scoped role-based access control (RBAC)  
- deterministic guard resolution  
- stakeholder-aware dashboard routing  
- infrastructure configuration and runtime behaviour  
- reproducible Laravel implementation patterns  

While the broader research programme explores domain modelling and academic system design, this repository provides the **generalised infrastructure components** that can be reused across Laravel applications beyond the original SIS prototype.

---

# Research Lineage

The relationship between the publications and this artefact can be summarised as follows:

```txt
Domain Review
│
├─ UK HE Levels, Awards, and Regulatory Oversight for TNE
│
System Architecture
│
├─ UK-Aligned SIS Design Prototype
│
Security Infrastructure
│
├─ Multi-Guard RBAC in Laravel
│
Reusable Software Artefact
│
└─ Laravel Stakeholder RBAC Infrastructure Artefact (this repository)
```

---

# Author

**Rukman Bernard**  
Independent Researcher  

ORCID  
https://orcid.org/0009-0001-2737-8367
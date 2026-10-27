# Sustainability claims frontend contract

This frontend separates a vendor's statement from a verified public claim. Selecting an environmental or social benefit never publishes it automatically.

## Required claim data

Every public claim records its exact wording, scope, evidence type, issuer/source, reference, valid-until or review date, supporting document, conditions, exclusions, review status, and public status.

## Status flow

Draft → Submitted → Under review → Evidence requested / Verified / Rejected / Expired. Only `Verified` claims may be `Published`. Editing wording, scope, evidence, or measurement creates a new review version; the last approved version remains visible until replaced or withdrawn.

## Claim-writing rules

- Avoid broad claims such as “eco-friendly”, “green”, “sustainable”, “zero impact”, or “chemical-free” without a precise qualification.
- State the product or operation covered, measured percentage or quantity, evidence source, reporting period, disposal conditions, and material limitations.
- Organic, recycled, biodegradable, compostable, plastic-free, vegan, cruelty-free, renewable-energy, fair-wage, carbon, and similar claims require relevant evidence.
- Compostable claims specify home or industrial conditions. Recyclable claims reflect realistically available collection and processing. Recycled-content claims state the percentage and basis of calculation.
- Carbon-neutral or avoided-emissions claims are intentionally not offered as simple checkboxes. They require a defined boundary, method, base year, calculation, reduction hierarchy, and independently reviewable evidence.

## Backend enforcement required later

The separate backend must authorize vendor/product ownership, privately store and malware-scan evidence, preserve versions, enforce expiry and review states, prevent unverified publication, record reviewer decisions, and retain an immutable audit trail. Frontend validation improves data quality but cannot verify a claim.

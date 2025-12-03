# Bond Delivery Programs Master Table

## Overview
The `bond_delivery_programs` table serves as the **master relationship table** for all programs, interventions, and initiatives in the livestock M&E system. It captures the complete hierarchy from Bond Delivery programs down to individual pillar-level programs and cross-cutting metrics.

## Table Structure

### Core Fields
- **id**: Primary key
- **code**: Unique identifier (BD1-BD16, P1.1-P10.9, CCM1-CCM4)
- **title**: Program name
- **description**: Detailed description
- **program_type**: `bond_delivery`, `pillar_program`, or `cross_cutting_metric`
- **program_category**: Thematic grouping (Production, Health, Infrastructure, etc.)

### Strategic Framework Relationships
Links to all levels of the strategic hierarchy:
- **presidential_priority_id**: Links to PP1-PP4
- **sectoral_goal_id**: Links to SG1-SG8
- **bond_outcome_id**: Links to BO1-BO8
- **pillar_ids**: JSON array supporting multiple pillars (P1-P10)

### Program Hierarchy
- **parent_program_id**: Self-referencing for sub-programs
  - Example: P1.1 (Dairy Value Chain Upgrading) → parent is P1 (Production Pillar)
  - Example: P2.3 (Disease Control and Eradication) → parent is P2 (Animal Health Pillar)

### Organizational Structure
- **department_id**: Responsible department
- **unit_owner**: Specific unit within department (e.g., "Dairy Development Unit", "PPP Unit")

### Implementation Tracking
- **baseline_year**: Start year
- **target_year**: Target completion year
- **budget_allocation**: Allocated budget (Decimal 15,2)
- **priority_level**: `high`, `medium`, `low`
- **implementation_status**: `planned`, `ongoing`, `completed`, `on_hold`, `cancelled`
- **is_active**: Boolean flag

### Metadata
- **source_document**: Reference document
- **implementation_notes**: Additional notes
- **timestamps**: created_at, updated_at
- **softDeletes**: deleted_at for soft deletion

## Program Taxonomy

### 1. Bond Delivery Programs (BD1-BD16)
Top-level programs that directly contribute to Bond Outcomes:

| Code | Title | Category | Department |
|------|-------|----------|------------|
| BD1 | Livestock Production Enhancement | Production | Ruminants & Monogastric Development |
| BD2 | Breed Improvement & Genetic Enhancement | Genetics | Ruminants & Monogastric Development |
| BD3 | Animal Health & Disease Control | Health | Animal Health & Reproductive Services |
| BD4 | Pasture & Feed Resource Development | Feed & Nutrition | Ranch & Pastoral Resources |
| BD5 | Extension Services & Farmer Training | Extension | Livestock Extension & Business Development |
| BD6 | Human Capital Development | Capacity Building | Common Services |
| BD7 | Emergency Response & Climate Resilience | Emergency Management | Veterinary Public Health |
| BD8 | Infrastructure Development | Infrastructure | Infrastructure, PPP & Special Programs |
| BD9 | Private Sector Engagement & PPP | Investment | Infrastructure, PPP & Special Programs |
| BD10 | Export Promotion & Market Access | Market Development | Livestock Extension & Business Development |
| BD11 | Data Collection & Statistics | Data & Information | Planning, Research & Statistics |
| BD12 | Business Development & Traceability | Business & Systems | Common Services |
| BD13 | Conflict Mitigation & Peace Building | Peace & Security | Infrastructure, PPP & Special Programs |
| BD14 | Dairy Development & Value Chain | Dairy | Ruminants & Monogastric Development |
| BD15 | Strategic Planning & NLGAS Development | Planning | Planning, Research & Statistics |
| BD16 | Citizens Engagement & Feedback | Governance | Common Services |

### 2. Pillar Programs (P1.1 - P10.9)
Sub-programs under each of the 10 NLGAS Pillars:

**Pillar 1: Production (P1.x)**
- P1.1: Dairy Value Chain Upgrading
- P1.2: Poultry Production Intensification
- P1.3: Small Ruminant Development
- P1.4: Beef Value Chain Enhancement
- P1.5: Pig Production Development
- P1.6: Hides & Skins Value Chain
- P1.7: Micro-Livestock & Apiculture

**Pillar 2: Animal Health (P2.x)**
- P2.1: Veterinary Inputs & Cold Chain
- P2.2: Surveillance & Early Warning
- P2.3: Disease Control & Eradication
- P2.4: Laboratory & Diagnostic Services
- P2.5: Veterinary Certification & Quarantine

**Pillar 3: Feed & Nutrition (P3.x)**
**Pillar 4: Water & Infrastructure (P4.x)**
**Pillar 5: Finance & Credit (P5.x)**
**Pillar 6: Conflict Mitigation (P6.x)**
**Pillar 7: Infrastructure & PPP (P7.x)**
**Pillar 8: Extension & Advisory Services (P8.x)**
**Pillar 9: Youth & Women Empowerment (P9.x)**
**Pillar 10: Data & Information Systems (P10.x)**

### 3. Cross-Cutting Metric Programs (CCM1-CCM4)
Transversal programs that span all pillars:

| Code | Title | Focus Area |
|------|-------|------------|
| CCM1 | Investment Mobilization & Resource Leveraging | Finance |
| CCM2 | Inter-Ministerial Coordination & Governance | Coordination |
| CCM3 | Environmental Sustainability & Climate Action | Environment |
| CCM4 | Social Inclusion & Equity | Social Inclusion |

## Model Relationships

### BondDeliveryProgram Model

```php
// Strategic Framework
presidentialPriority() // BelongsTo
sectoralGoal()         // BelongsTo
bondOutcome()          // BelongsTo
getPillarsAttribute()  // Accessor for pillar_ids JSON

// Organizational
department()           // BelongsTo

// Hierarchy
parentProgram()        // BelongsTo (self)
subPrograms()          // HasMany (self)

// Indicators
indicators()           // HasMany (Indicator model)
```

### Indicator Relationship
Indicators now link to `BondDeliveryProgram` via `bond_delivery_program_id`:
- BOI indicators → BD programs
- PPOI indicators → Pillar programs (P1.1, P2.3, etc.)
- CCM indicators → Cross-cutting programs

## Query Examples

```php
// Get all Bond Delivery programs
$bondPrograms = BondDeliveryProgram::bondDelivery()->get();

// Get all Pillar programs under P1
$p1Programs = BondDeliveryProgram::pillarProgram()
    ->where('code', 'LIKE', 'P1.%')
    ->get();

// Get all programs for a specific Priority
$pp1Programs = BondDeliveryProgram::byPriority($pp1Id)->get();

// Get program hierarchy
$program = BondDeliveryProgram::where('code', 'P1.1')->first();
echo $program->getFullHierarchy(); // "P1 > P1.1"

// Get all indicators for a program
$indicators = $program->indicators;

// Get all sub-programs
$subPrograms = $program->subPrograms;
```

## Benefits

1. **Single Source of Truth**: All program relationships in one table
2. **Hierarchical Structure**: Parent-child relationships maintain program taxonomy
3. **Flexible Pillar Assignment**: JSON array allows programs to span multiple pillars
4. **Complete Traceability**: Full links from PP → SG → BO → Pillars → Programs → Indicators
5. **Department Accountability**: Clear ownership and unit-level responsibility
6. **Implementation Tracking**: Status, budget, and timeline in one place
7. **Cross-Cutting Support**: CCM programs can span all pillars
8. **Soft Deletes**: Preserve historical data while marking inactive programs

## Data Migration Notes

Based on the master catalog CSV:
- **465 total indicators** mapped across programs
- **Bond Output Indicators (BOI-1 to BOI-57)** → Link to BD1-BD16
- **Pillar Program Output Indicators (PPOI-x.x.x)** → Link to Px.x programs
- **Cross-Cutting Metrics (PPOI-CCM)** → Link to CCM1-CCM4

## Next Steps

1. ✅ Create migration and model
2. ✅ Seed Bond Delivery Programs (BD1-BD16)
3. ⏳ Seed Cross-Cutting Metric programs (CCM1-CCM4)
4. ⏳ Seed Pillar Programs (P1.1-P10.9) with parent relationships
5. ⏳ Update existing indicators to link to appropriate programs
6. ⏳ Create controller and API endpoints
7. ⏳ Create Vue pages for program management
8. ⏳ Add program hierarchy visualization

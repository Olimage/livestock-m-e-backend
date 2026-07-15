<?php

namespace Database\Seeders;

use App\Models\ImpactIndicator;
use App\Models\OutcomeIndicator;
use App\Models\OutputIndicator;
use Illuminate\Database\Seeder;

/**
 * Seeds the canonical FMLD baseline indicators (Impact, Outcome, Output)
 * from "indicators updated.xlsx" — sheet "Baseline Indicators v2.2".
 *
 * Bond Output (BOI-*) and Pillar Program Output (PPOI-*) indicators are not
 * part of this list and are seeded elsewhere.
 */
class ResultChainIndicatorSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedType(ImpactIndicator::class, $this->impactIndicators());
        $this->seedType(OutcomeIndicator::class, $this->outcomeIndicators());
        $this->seedType(OutputIndicator::class, $this->outputIndicators());

        $this->command?->info(sprintf(
            'Result Chain indicators seeded: %d impact, %d outcome, %d output.',
            count($this->impactIndicators()),
            count($this->outcomeIndicators()),
            count($this->outputIndicators()),
        ));
    }

    /**
     * Upsert each indicator by its canonical code.
     *
     * The RC models auto-generate `code` (PREFIX-{id}) on create, so after
     * creating we force the spreadsheet code via updateQuietly() to keep the
     * codes exactly as authored (IMP-1, OUT-1, OPT-1, ...). Keyed by code, so
     * re-running updates titles/descriptions in place instead of duplicating.
     *
     * @param  class-string  $modelClass
     * @param  array<int, array{code:string, title:string, description:string}>  $rows
     */
    private function seedType(string $modelClass, array $rows): void
    {
        foreach ($rows as $row) {
            $description = $row['description'] !== '' ? $row['description'] : null;

            $existing = $modelClass::withTrashed()->where('code', $row['code'])->first();

            if ($existing) {
                $existing->update([
                    'title' => $row['title'],
                    'description' => $description,
                ]);

                continue;
            }

            $model = $modelClass::create([
                'title' => $row['title'],
                'description' => $description,
            ]);

            $model->updateQuietly(['code' => $row['code']]);
        }
    }

    /** @return array<int, array{code:string, title:string, description:string}> */
    private function impactIndicators(): array
    {
        return [
            ['code' => 'IMP-1', 'title' => 'Proportion of livestock households below poverty line', 'description' => 'The percentage of livestock-keeping households (livestock farming contributes to at least 70% of sustaining revenue) whose income levels fall below the nationally defined poverty line.'],
            ['code' => 'IMP-2', 'title' => '% of population with access to livestock products', 'description' => 'Measures the proportion of the population that has physical and economic access to livestock products (meat, milk, eggs) within a defined geographic area and time period.'],
            ['code' => 'IMP-3', 'title' => 'Consumer Price Index for Livestock Products', 'description' => 'Measures changes over time in the average prices paid by consumers for a basket of livestock products, reflecting affordability and market stability.'],
            ['code' => 'IMP-4', 'title' => 'Animal-Source Food Nutrition Adequacy Score (ASF-NAS)', 'description' => 'Measures the extent to which consumption of animal-source foods meets recommended dietary requirements for key nutrients, serving as a proxy for diet quality and nutritional adequacy.'],
        ];
    }

    /** @return array<int, array{code:string, title:string, description:string}> */
    private function outcomeIndicators(): array
    {
        return [
            ['code' => 'OUT-1', 'title' => '% contribution to GDP (Livestock Sector)', 'description' => 'Measures the economic significance of the national livestock sector by calculating the proportion of the country\'s total Gross Domestic Product (GDP) that is directly attributable to all livestock subsectors and their related value chains.'],
            ['code' => 'OUT-2', 'title' => 'Annual Livestock Export Earnings (FX)', 'description' => 'Quantifies the total foreign exchange earnings generated from the export of live animals and livestock products (meat, milk, eggs, hides, skins, leather, and other by-products) within a financial year.'],
            ['code' => 'OUT-3', 'title' => 'Volume of livestock products/by-products exported.', 'description' => 'Measures the total quantity of livestock and livestock-derived products exported from the country, including live animals, meat, milk, eggs, hides, and skins.'],
            ['code' => 'OUT-4', 'title' => 'Volume of livestock products/by-products imported.', 'description' => 'Measures the total quantity of livestock and livestock-derived products imported into the country, including live animals, meat, milk, eggs, hides, and skins.'],
            ['code' => 'OUT-5', 'title' => 'Number of jobs created in the livestock sector', 'description' => 'Total number of direct and indirect jobs generated across the livestock value chain within a defined period. It includes employment in production (farming), processing, transportation, trading, feed manufacturing, veterinary services, and allied input supply.'],
            ['code' => 'OUT-6', 'title' => 'Average Income Change for Women/Youth in Programs', 'description' => 'Measures the change in average income earned by women and youth who participate in livestock-related programs, projects, or interventions over a defined implementation period'],
            ['code' => 'OUT-7', 'title' => 'Livestock Credit as % of Total Agricultural Lending', 'description' => 'Measure and track the financial support directed towards the livestock sector within the broader agricultural landscape'],
            ['code' => 'OUT-8', 'title' => 'Daily Animal Protein Consumption per capita', 'description' => 'Measures the average amount of animal-source protein consumed per person per day within the country. It captures both the volume of animal-source foods available for consumption and their proportional dietary contribution relative to population size.'],
            ['code' => 'OUT-9', 'title' => 'Total number of animals by species (cattle, goat, sheep, camel, poultry, pigs)', 'description' => 'Measures the total population of major livestock species within the country capturing the size, structure, and distribution of the national herd and flock, (including dissagegation by lactating animals)'],
            ['code' => 'OUT-10', 'title' => 'Average Slaughter Weight per Cattle', 'description' => 'Measures the average live weight of cattle at the point of slaughter within formal and informal abattoirs reflecting nutritional status, breed quality, feeding systems, management practices, and overall production efficiency.'],
            ['code' => 'OUT-11', 'title' => 'Average Slaughter Weight per Goat', 'description' => 'Measures the average live weight per goat at the point of slaughter across formal and informal slaughter facilities reflecting the productivity, market readiness, and overall performance of goat production systems'],
            ['code' => 'OUT-12', 'title' => 'Average Slaughter Weight per Pig', 'description' => 'Measures the average live weight of pigs at the time of slaughter during a specified reporting period providing a reliable metric of pig growth performance, feeding quality, health status, and production efficiency'],
            ['code' => 'OUT-13', 'title' => 'Average Slaughter Weight per Poultry', 'description' => 'Measures the average live weight of poultry at the time of slaughter reflecting production efficiency, feed conversion performance, and overall flock health'],
            ['code' => 'OUT-14', 'title' => 'Total national meat production (all species)', 'description' => 'Measures the total volume of meat produced annually from all livestock species, including cattle, goats, sheep, pigs, and poultry'],
            ['code' => 'OUT-15', 'title' => 'Average Daily Milk Yield Per Lactating Specie (Cow, Goat)', 'description' => 'Measures the average volume of milk produced per lactating species (cow,goat,sheep) per day reflecting dairy productivity and efficiency within the livestock sector.'],
            ['code' => 'OUT-16', 'title' => 'Total national milk production', 'description' => 'Measures the total volume of milk produced nationally from all lactating species—predominantly cattle, goats, and sheep—over a one-year period. It includes milk consumed on-farm, sold in formal markets, traded in informal markets, or processed into dairy products.'],
            ['code' => 'OUT-17', 'title' => 'Volume of Honey produced annually', 'description' => 'Measures the total quantity of honey produced across the country within a calendar year, including production from traditional, transitional, and modern beekeeping systems.'],
            ['code' => 'OUT-18', 'title' => 'Total national egg production', 'description' => 'Measures the total quantity of eggs produced within the country across all poultry production systems—including commercial layer farms, backyard poultry, and semi-intensive operations—within a defined reporting period.'],
            ['code' => 'OUT-19', 'title' => 'Volume of commercially produced feed', 'description' => 'Measures the total annual quantity of livestock feed produced by commercial feed manufacturers nationally. It reflects the size, capacity, and growth trajectory of the feed industry,'],
            ['code' => 'OUT-20', 'title' => 'Total commercial feed production capacity', 'description' => 'Measures the total annual production capacity of commercially manufactured livestock feed in the country, including compound feed, concentrates, and premixes.'],
            ['code' => 'OUT-21', 'title' => '% of Abattoirs Meeting National Standards', 'description' => 'Measures the percentage of abattoirs in the country that meet nationally defined hygiene, operational, and structural standards'],
            ['code' => 'OUT-22', 'title' => 'Livestock Mortality rate due to TADs by Species (Cattle,Goat,Sheep,Pig,Poultry)', 'description' => 'Measures the proportion of livestock deaths attributable to Transboundary Animal Diseases (TADs) within a specified period, disaggregated by species (cattle, goats, sheep, pigs, poultry). It reflects the burden of TAD-related mortality and the effectiveness of disease prevention and control measures.'],
            ['code' => 'OUT-23', 'title' => '% of animals vaccinated against TADs - CVPP, FMD, ASF, NCD, PPR, Antrax', 'description' => 'Measures the proportion of susceptible livestock populations that have been vaccinated against priority Transboundary Animal Diseases (TADs), including CVPP, FMD, ASF, NCD, PPR, and Anthrax. It reflects vaccination coverage and effectiveness of disease prevention programmes.'],
            ['code' => 'OUT-24', 'title' => 'Prevalance rate of priority TADs (CVPP, FMD, ASF, NCD, PPR, HPAI, Antrax) across species', 'description' => 'Measures the proportion of animals infected with priority Transboundary Animal Diseases (TADs) at a given point in time or over a specified period. It reflects the level of disease presence within livestock populations and informs surveillance and control strategies.'],
            ['code' => 'OUT-25', 'title' => 'Livestock Extension Worker to Farmer Ratio', 'description' => 'Measures the average number of livestock farmers served by a single livestock extension worker reflecting the capacity of the extension system to deliver advisory, technical, and veterinary services to farmers.'],
            ['code' => 'OUT-26', 'title' => 'Veterinarians-to-livestock ratio', 'description' => 'Measures the ratio of qualified veterinarians to the total livestock population, reflecting the adequacy of veterinary service coverage.'],
            ['code' => 'OUT-27', 'title' => 'Animal Scientists-to-livestock ratio', 'description' => 'Measures the availability of qualified animal science professionals relative to the total livestock population within the country. Animal scientists include professionals trained in animal production, breeding, nutrition, and livestock management who are actively engaged in research, extension services, breeding programs, feed development, and livestock production systems. The ratio reflects the level of technical expertise available to support livestock productivity, genetic improvement, feed optimization, and overall sector development.'],
            ['code' => 'OUT-28', 'title' => 'Veterinary Paraprofessionals-to-livestock ratio', 'description' => 'Measures the availability of trained veterinary paraprofessionals relative to the livestock population. Veterinary paraprofessionals include animal health technicians, veterinary assistants, and other trained personnel who support veterinarians in delivering animal health services such as vaccination, disease surveillance, treatment, and preventive care.'],
            ['code' => 'OUT-29', 'title' => 'Community Animal Health Workers-to-livestock ratio', 'description' => 'Measures the number of Community Animal Health Workers (CAHWs) available relative to the livestock population. CAHWs are locally trained individuals who provide basic animal health services, disease surveillance, vaccination support, and advisory services at the community level, particularly in underserved pastoral and rural areas.'],
            ['code' => 'OUT-30', 'title' => '% of livestock related conflicts resolved by functional conflict mitigation committees', 'description' => 'Measures the proportion of livestock-related conflicts that are successfully resolved by established conflict mitigation committees at community, regional, or national levels.'],
            ['code' => 'OUT-31', 'title' => 'Farmer-Herder Conflict Response Time (Days)', 'description' => 'Measures the average number of days taken to respond to reported livestock-related conflicts between farmers and herders, from the time of report to the initiation of resolution or intervention.'],
        ];
    }

    /** @return array<int, array{code:string, title:string, description:string}> */
    private function outputIndicators(): array
    {
        return [
            ['code' => 'OPT-1', 'title' => 'Number of livestock related conflicts reported', 'description' => 'The total number of reported incidents of conflict directly involving livestock production, grazing, pastoral mobility, resource competition, or market disputes within a defined period.'],
            ['code' => 'OPT-2', 'title' => 'Number of reported outbreaks of animals diseases', 'description' => 'Measures the total number of confirmed and officially reported outbreaks of animal diseases within a defined reporting period. An outbreak refers to the occurrence of one or more cases of a disease within a specific geographic location, herd, or population of animals that exceeds the expected normal incidence.'],
            ['code' => 'OPT-3', 'title' => 'Number of functional watering points in Grazing Reserves and Stock Routes.', 'description' => 'Measures the adequacy of water infrastructure in grazing reserves and on stock routes by calculating the number of functional livestock watering points available relative to the total animal population. To be disaggregated by Grazing Reserves and Stock Route'],
            ['code' => 'OPT-4', 'title' => 'Hectares of land cultivated for pasture production', 'description' => 'Measures the total land area that is actively cultivated or managed for the production of improved pasture and forage crops intended for livestock feeding. It includes land planted with pasture grasses, legumes, fodder crops, and other cultivated forage species used to support livestock nutrition under both commercial and smallholder production systems.'],
            ['code' => 'OPT-5', 'title' => 'Number of Community Animal Health Workers (CAHWs) Deployed and Active', 'description' => 'Measures the total number of trained Community Animal Health Workers (CAHWs) who are operational and actively providing basic veterinary and animal health services within their communities'],
            ['code' => 'OPT-6', 'title' => 'Number of Veterinary Paraprofessionals Deployed and Active', 'description' => 'Measures the total number of trained veterinary paraprofessionals who are officially deployed and actively providing animal health services within the livestock sector during the reporting period. Veterinary paraprofessionals include animal health technicians, veterinary assistants, and other certified mid-level professionals who support veterinarians in delivering animal health services such as disease diagnosis, vaccination, treatment, surveillance, and advisory services to livestock producers'],
            ['code' => 'OPT-7', 'title' => 'Number of Animal Scientists Deployed and Active', 'description' => 'Measures the total number of qualified animal scientists who are formally deployed and actively engaged in livestock production, research, extension, or advisory services within the livestock sector during the reporting period.'],
            ['code' => 'OPT-8', 'title' => 'Number of Veterinarians Deployed and Active', 'description' => 'Measures the total number of licensed veterinarians who are officially deployed and actively providing veterinary services within the livestock sector during the reporting period.'],
            ['code' => 'OPT-9', 'title' => 'Number of Animals Registered in the National Animal Identification and Traceability System (NAITS)', 'description' => 'Measures the total number of animals registered in the National Animal Identification and Traceability System (NAITS), a system designed to uniquely identify animals for traceability, disease control, and market transparency'],
            ['code' => 'OPT-10', 'title' => 'Number of Functional Abattoirs/Slaughter slabs/Slaughter House (Disaggregate; Private, Public,PPP)', 'description' => 'Measures the total number of abattoirs, slaughter slabs, and slaughter houses that are operational and meet minimum standards for hygiene, food safety, and animal welfare within the reporting period. A facility is considered functional if it is actively processing animals and complies with relevant regulatory and sanitary requirements.'],
            ['code' => 'OPT-11', 'title' => 'Number of operational breeding farms/AI centers', 'description' => 'Measures the total number of breeding farms and Artificial Insemination (AI) centres that are operational and actively providing genetic improvement services to livestock producers within the reporting period. Operational entities include breeding farms engaged in controlled breeding and multiplication of improved livestock, as well as AI centres providing semen production, storage, distribution, and insemination services, supported by qualified personnel and functional infrastructure. To be disaggregated by Private, Public and PPP'],
            ['code' => 'OPT-12', 'title' => 'Total commercial cold storage capacity for livestock products', 'description' => 'Measures the total storage capacity available for perishable livestock products, including meat, milk, eggs, and other processed products, in refrigerated or frozen facilities.'],
            ['code' => 'OPT-13', 'title' => 'Total cold storage capacity of livestock biologicals in government facilities', 'description' => 'Measures the total cold storage capacity available within government-owned or government-managed facilities for the storage and preservation of livestock biological products. Livestock biologicals include vaccines, diagnostic reagents, sera, and other temperature-sensitive veterinary biological materials used for disease prevention, control, and surveillance.'],
            ['code' => 'OPT-14', 'title' => 'Number of functional commercial feedmills', 'description' => 'Measures the total number of commercial feedmills that are operational and producing livestock feed at or near capacity.'],
            ['code' => 'OPT-15', 'title' => 'Number of functional Veterinary Labs/Diagnostic Centres', 'description' => 'Measures the total number of veterinary laboratories and diagnostic centres that are operational and capable of conducting disease diagnostics, surveillance, and antimicrobial resistance (AMR) testing. This includes national, state, private, and AMR surveillance laboratories contributing to animal health systems.'],
            ['code' => 'OPT-16', 'title' => 'Number of functional Veterinary facilities/practices (Ambulatory, Clinics, Hospitals, Vet Teaching Hospitals)', 'description' => 'Measures the total number of veterinary facilities and practices that are operational and providing animal health services within the reporting period. These include ambulatory services, veterinary clinics, hospitals, and veterinary teaching hospitals. A facility or practice is considered functional if it is actively delivering veterinary services, staffed with qualified personnel, and equipped to provide clinical, preventive, and/or advisory services.'],
            ['code' => 'OPT-17', 'title' => 'Value of Private/PPP Investment Mobilized', 'description' => 'Measures the total monetary value of private sector and Public-Private Partnership (PPP) investments mobilized for livestock production, processing, and related value chain activities.'],
            ['code' => 'OPT-18', 'title' => 'Number of Commercial livestock Farms with Active Insurance', 'description' => 'Measures the total number of commercial livestock farmers who have enrolled in and maintain active insurance coverage for their animals and production risks.'],
            ['code' => 'OPT-19', 'title' => 'Number of Livestock insured', 'description' => 'Measures the total number of livestock covered by formal insurance policies that provide financial protection against risks such as disease, theft, or natural disasters.'],
            ['code' => 'OPT-20', 'title' => 'Number of Conflict Mitigation Committee Members Trained', 'description' => 'Measures the total number of individuals serving on livestock conflict mitigation committees who have received formal training on dispute resolution, mediation, and conflict management.'],
            ['code' => 'OPT-21', 'title' => 'Number of Transhumance Routes with Formalized Access Agreements', 'description' => 'Measures the total number of pastoralist and livestock transhumance routes that have legally recognized and documented agreements for movement, grazing, and water access'],
            ['code' => 'OPT-22', 'title' => 'Number of Livestock Enterprises Owned by Women and Youth', 'description' => 'Measures the number of registered or surveyed livestock enterprises that are owned or managed by women and youth, highlighting inclusivity and demographic participation in the sector.'],
            ['code' => 'OPT-23', 'title' => 'Number of Women/Youth Accessing Livestock Finance', 'description' => 'Measures the total number of women and youth who have successfully obtained formal or program-supported finance for livestock production, processing, or value chain activities'],
            ['code' => 'OPT-24', 'title' => 'Number of Livestock Cottage Industries Established', 'description' => 'Measures the total number of small-scale, often family-owned livestock-related enterprises established within a specified period, including processing, feed production, and value-added services.'],
            ['code' => 'OPT-25', 'title' => 'Number of States reporting through NLIMS', 'description' => 'Measures the total number of states that are actively submitting livestock-related data through the National Livestock Information Management System (NLIMS) within a defined reporting period. A state is considered to be reporting through NLIMS if it regularly uploads validated livestock data—such as animal population records, disease surveillance reports, vaccination activities, livestock movement data, or production statistics—into the system in accordance with established reporting protocols.'],
            ['code' => 'OPT-26', 'title' => 'Frequency of National Livestock Statistics Publication by FMLD', 'description' => 'Measures the number of times the Federal Ministry of Livestock Development (FMLD) officially publishes livestock statistics within a calendar year.'],
            ['code' => 'OPT-27', 'title' => 'Number of Livestock Farmers Enrolled in National Database', 'description' => 'Measures the number of livestock farmers registered in the national livestock database or management system, reflecting the coverage and adoption of formal registration systems.'],
            ['code' => 'OPT-28', 'title' => 'Average interest rate for livestock loans', 'description' => 'Measures the mean interest rate charged on loans provided to livestock farmers by formal financial institutions'],
            ['code' => 'OPT-29', 'title' => 'Number of livestock farmers accessing formal credit', 'description' => 'Measures the total number of livestock farmers who have obtained formal loans from banks, microfinance institutions, or other accredited financial providers.'],
            ['code' => 'OPT-30', 'title' => 'Number of live animals exported', 'description' => 'Measures the total number of live livestock animals exported from the country to international markets within a defined reporting period. It captures animals transported across national borders for purposes such as breeding, slaughter, fattening, or further production in destination countries.'],
            ['code' => 'OPT-31', 'title' => 'Number of Quarantine station coverage at border crossings', 'description' => 'Measures the total number of quarantine stations that are operational at official border points to monitor, inspect, and control the movement of livestock entering or leaving the country.'],
            ['code' => 'OPT-32', 'title' => 'Number of functional milk collection centers established and operational', 'description' => ''],
        ];
    }
}

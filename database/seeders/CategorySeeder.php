<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Seeds the industry categories plus per-language content
     * (name, meta title, meta description, HTML description) for
     * every language in the `languages` table. Safe to re-run.
     */
    public function run(): void
    {
        // Categories need the default languages seeded first.
        if (DB::table('languages')->count() === 0) {
            $this->call(LanguageSeeder::class);
        }

        $languages = DB::table('languages')->pluck('id', 'code'); // ['en' => 1, 'ko' => 2, 'ja' => 3, 'zh' => 4, 'es' => 5, 'de' => 6, 'fr' => 7]

        foreach ($this->categories() as $index => $cat) {
            $categoryId = DB::table('categories')->where('slug', $cat['slug'])->value('id');

            if (!$categoryId) {
                $categoryId = DB::table('categories')->insertGetId([
                    'slug'       => $cat['slug'],
                    'sort_order' => $index + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('categories')->where('id', $categoryId)->update([
                    'sort_order' => $index + 1,
                    'updated_at' => now(),
                ]);
            }

            foreach ($cat['translations'] as $code => $t) {
                if (!isset($languages[$code])) {
                    continue;
                }

                DB::table('category_translations')->updateOrInsert(
                    ['category_id' => $categoryId, 'language_id' => $languages[$code]],
                    [
                        'name'             => $t['name'],
                        'meta_title'       => $t['meta_title'],
                        'meta_description' => $t['meta_description'],
                        'description'      => $t['description'],
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ]
                );
            }
        }
    }

    private function categories(): array
    {
        return [
            [
                'slug' => 'energy-and-power',
                'translations' => [
                    'en' => [
                        'name' => 'Energy and Power',
                        'meta_title' => 'Energy and Power Market Research Reports & Analysis',
                        'meta_description' => 'Data-driven Energy and Power market research covering power generation, renewables, oil and gas, grid infrastructure and energy storage, with market sizing, forecasts and competitive analysis.',
                        'description' => '<h2>Energy and Power Market Research and Industry Insights</h2><p>The energy and power sector is being reshaped by decarbonization goals, the electrification of transport and industry, and large-scale grid modernization. Our research spans conventional and renewable generation, nuclear, transmission and distribution, energy storage, and the wider fuels value chain across every major region.</p><p>Each report pairs detailed market sizing and multi-year forecasts with analysis of policy drivers, technology cost trends, supply chain constraints, and the strategies of utilities, independent power producers and equipment suppliers. These insights help operators, investors and technology vendors prioritize markets, quantify risk and time capital decisions.</p>',
                    ],
                    'ja' => [
                        'name' => 'エネルギー・電力',
                        'meta_title' => 'エネルギー・電力の市場調査レポートと業界分析',
                        'meta_description' => '発電、再生可能エネルギー、原子力、石油・ガス、送配電網、エネルギー貯蔵を対象とするエネルギー・電力分野の市場調査レポートです。市場規模の推計、複数年の需要予測、政策・技術動向、サプライチェーン、主要企業の競合分析を地域別に提供し、事業者・投資家・技術ベンダーの市場評価と投資判断、事業戦略の策定を支援します。',
                        'description' => '<h2>エネルギー・電力の市場調査と業界インサイト</h2><p>エネルギー・電力分野は、脱炭素化目標、輸送・産業の電化、送電網の大規模な近代化によって構造的に変化しています。当社の調査は、従来型発電と再生可能エネルギー、原子力、送配電、エネルギー貯蔵、さらに幅広い燃料バリューチェーンを主要地域ごとに網羅します。</p><p>各レポートは、詳細な市場規模の推計と複数年予測に加え、政策要因、技術コストの動向、サプライチェーンの制約、電力会社・独立系発電事業者・機器サプライヤーの戦略を分析します。これにより、事業者、投資家、技術ベンダーは市場の優先順位付け、リスクの定量化、投資タイミングの判断を行えます。</p>',
                    ],
                    'ko' => [
                        'name' => '에너지 및 전력',
                        'meta_title' => '에너지 및 전력 시장 조사 보고서 및 산업 분석',
                        'meta_description' => '발전, 재생에너지, 원자력, 석유·가스, 전력망 인프라, 에너지 저장을 아우르는 에너지 및 전력 부문 시장 조사 보고서입니다. 시장 규모 추정, 다년간 수요 전망, 정책·기술 동향, 공급망, 주요 기업의 경쟁 분석을 지역별로 제공하여 사업자·투자자·기술 공급업체의 시장 평가와 투자 판단, 사업 전략 수립을 지원합니다.',
                        'description' => '<h2>에너지 및 전력 시장 조사 및 산업 인사이트</h2><p>에너지 및 전력 부문은 탈탄소화 목표, 운송·산업의 전기화, 대규모 전력망 현대화로 구조적 전환을 겪고 있습니다. 당사의 조사는 전통적 발전과 재생에너지, 원자력, 송배전, 에너지 저장, 그리고 광범위한 연료 밸류체인을 주요 지역별로 다룹니다.</p><p>각 보고서는 상세한 시장 규모 추정과 다년간 전망에 더해 정책 요인, 기술 비용 추세, 공급망 제약, 전력회사·민자 발전사업자·설비 공급업체의 전략을 분석합니다. 이를 통해 사업자, 투자자, 기술 공급업체는 시장 우선순위 설정, 리스크 정량화, 투자 시점 결정을 내릴 수 있습니다.</p>',
                    ],
                    'zh' => [
                        'name' => '能源与电力',
                        'meta_title' => '能源与电力市场调研报告与行业分析',
                        'meta_description' => '本报告聚焦能源与电力领域的市场调研，涵盖传统发电、可再生能源、核能、石油天然气、输配电网络及储能。报告按地区提供市场规模测算、多年需求预测、政策与技术成本趋势、供应链制约因素及主要企业竞争格局的深入分析，为运营商、投资者和技术供应商的市场评估、风险量化与投资决策提供依据，并系统梳理全球主要区域市场竞争格局，助力企业把握增长机遇。',
                        'description' => '<h2>能源与电力市场调研与行业洞察</h2><p>在脱碳目标、交通与工业电气化以及大规模电网现代化的推动下，能源与电力行业正经历结构性变革。我们的研究涵盖传统发电与可再生能源发电、核能、输配电、储能，以及各主要地区更广泛的燃料价值链。</p><p>每份报告不仅提供详尽的市场规模测算和多年预测，还分析政策驱动因素、技术成本趋势、供应链制约，以及公用事业公司、独立发电商和设备供应商的战略。这些洞察帮助运营商、投资者和技术供应商确定市场优先级、量化风险并把握投资时机。</p>',
                    ],
                    'es' => [
                        'name' => 'Energía y Electricidad',
                        'meta_title' => 'Informes de Investigación de Mercado de Energía y Electricidad',
                        'meta_description' => 'Investigación de mercado de Energía y Electricidad: generación, renovables, petróleo y gas, redes y almacenamiento, con dimensionamiento, previsiones y análisis competitivo por región.',
                        'description' => '<h2>Investigación de Mercado y Perspectivas del Sector de Energía y Electricidad</h2><p>El sector de energía y electricidad está siendo transformado por los objetivos de descarbonización, la electrificación del transporte y la industria, y la modernización a gran escala de las redes eléctricas. Nuestra investigación abarca la generación convencional y renovable, la energía nuclear, la transmisión y distribución, el almacenamiento de energía y la cadena de valor de los combustibles en todas las regiones principales.</p><p>Cada informe combina un dimensionamiento detallado del mercado y previsiones plurianuales con el análisis de los factores políticos, las tendencias de costes tecnológicos, las limitaciones de la cadena de suministro y las estrategias de las empresas de servicios públicos, los productores independientes de energía y los proveedores de equipos. Estos datos ayudan a operadores, inversores y proveedores tecnológicos a priorizar mercados y planificar sus inversiones.</p>',
                    ],
                    'de' => [
                        'name' => 'Energie und Strom',
                        'meta_title' => 'Marktforschungsberichte und Branchenanalyse Energie und Strom',
                        'meta_description' => 'Datenbasierte Marktforschung zu Energie und Strom: Stromerzeugung, erneuerbare Energien, Öl und Gas, Netzinfrastruktur und Energiespeicherung, mit Marktgröße, Prognosen und Wettbewerbsanalyse.',
                        'description' => '<h2>Marktforschung und Brancheneinblicke Energie und Strom</h2><p>Der Energie- und Stromsektor wird durch Dekarbonisierungsziele, die Elektrifizierung von Verkehr und Industrie sowie die groß angelegte Modernisierung der Stromnetze grundlegend verändert. Unsere Forschung umfasst konventionelle und erneuerbare Erzeugung, Kernenergie, Übertragung und Verteilung, Energiespeicherung sowie die breitere Wertschöpfungskette fossiler Brennstoffe in allen wichtigen Regionen.</p><p>Jeder Bericht verbindet eine detaillierte Marktgrößenschätzung und mehrjährige Prognosen mit einer Analyse politischer Treiber, Technologiekostenentwicklungen, Lieferkettenbeschränkungen sowie der Strategien von Versorgern, unabhängigen Stromerzeugern und Ausrüstungslieferanten. Diese Erkenntnisse helfen Betreibern, Investoren und Technologieanbietern, Märkte zu priorisieren und Investitionsentscheidungen zeitlich zu planen.</p>',
                    ],
                    'fr' => [
                        'name' => 'Énergie et Électricité',
                        'meta_title' => 'Rapports d\'Études de Marché Énergie et Électricité',
                        'meta_description' => 'Étude de marché sur l\'Énergie et l\'Électricité : production, renouvelables, pétrole et gaz, infrastructures de réseau et stockage, avec prévisions et analyse concurrentielle par région.',
                        'description' => '<h2>Étude de Marché et Perspectives du Secteur de l\'Énergie et de l\'Électricité</h2><p>Le secteur de l\'énergie et de l\'électricité est transformé par les objectifs de décarbonation, l\'électrification des transports et de l\'industrie, ainsi que la modernisation à grande échelle des réseaux électriques. Notre étude couvre la production conventionnelle et renouvelable, le nucléaire, le transport et la distribution, le stockage d\'énergie et l\'ensemble de la chaîne de valeur des combustibles dans toutes les grandes régions.</p><p>Chaque rapport associe un dimensionnement détaillé du marché et des prévisions pluriannuelles à une analyse des facteurs politiques, des tendances de coûts technologiques, des contraintes de la chaîne d\'approvisionnement, ainsi que des stratégies des compagnies d\'électricité, des producteurs indépendants et des fournisseurs d\'équipements. Ces analyses aident les exploitants, investisseurs et fournisseurs de technologies à prioriser les marchés et planifier leurs investissements.</p>',
                    ],
                ],
            ],
            [
                'slug' => 'chemicals-and-materials',
                'translations' => [
                    'en' => [
                        'name' => 'Chemicals and Materials',
                        'meta_title' => 'Chemicals and Materials Market Research & Analysis',
                        'meta_description' => 'Comprehensive Chemicals and Materials market research across commodity and specialty chemicals, polymers, advanced materials and coatings, with demand forecasts and competitive benchmarking by region.',
                        'description' => '<h2>Chemicals and Materials Market Research and Industry Insights</h2><p>The chemicals and materials industry underpins nearly every manufacturing value chain, from packaging and construction to electronics, mobility and healthcare. Our coverage includes commodity and specialty chemicals, petrochemicals, polymers and plastics, advanced and composite materials, adhesives and coatings, with a close view of feedstock economics and sustainability pressures.</p><p>Reports combine granular market sizing and forecasts with analysis of raw material pricing, capacity additions, regulatory shifts and end-use demand. The findings support producers, distributors and downstream manufacturers in planning investments, optimizing portfolios and responding to circular-economy and emissions requirements.</p>',
                    ],
                    'ja' => [
                        'name' => '化学・材料',
                        'meta_title' => '化学・材料の市場調査レポートと業界分析',
                        'meta_description' => 'コモディティ・スペシャリティ化学品、石油化学、ポリマー・プラスチック、先端材料・複合材料、接着剤、コーティングを対象とする化学・材料分野の市場調査レポートです。市場規模と需要予測、原料価格や設備増設、規制動向、最終用途需要、主要企業の競合ベンチマークを地域別に提供し、投資計画とポートフォリオ戦略の策定を支援します。',
                        'description' => '<h2>化学・材料の市場調査と業界インサイト</h2><p>化学・材料産業は、包装や建設から電子機器、モビリティ、ヘルスケアに至るまで、ほぼすべての製造バリューチェーンを支えています。当社の調査対象は、コモディティおよびスペシャリティ化学品、石油化学、ポリマー・プラスチック、先端材料・複合材料、接着剤、コーティングであり、原料経済性とサステナビリティへの圧力を注視します。</p><p>各レポートは、詳細な市場規模と予測に加え、原材料価格、設備増設、規制の変化、最終用途需要を分析します。これにより、メーカー、流通業者、川下の製造業者は投資計画、ポートフォリオ最適化、循環経済および排出削減要件への対応を進められます。</p>',
                    ],
                    'ko' => [
                        'name' => '화학 및 소재',
                        'meta_title' => '화학 및 소재 시장 조사 보고서 및 산업 분석',
                        'meta_description' => '범용·스페셜티 화학, 석유화학, 고분자·플라스틱, 첨단·복합 소재, 접착제, 코팅을 아우르는 화학 및 소재 부문 시장 조사 보고서입니다. 시장 규모와 수요 전망, 원료 가격과 설비 증설, 규제 동향, 최종 수요, 주요 기업의 경쟁 벤치마킹을 지역별로 제공하여 투자 계획과 포트폴리오 전략 수립을 지원합니다.',
                        'description' => '<h2>화학 및 소재 시장 조사 및 산업 인사이트</h2><p>화학 및 소재 산업은 포장과 건설에서 전자, 모빌리티, 헬스케어에 이르기까지 거의 모든 제조 밸류체인을 뒷받침합니다. 당사의 조사 범위는 범용 및 스페셜티 화학, 석유화학, 고분자·플라스틱, 첨단·복합 소재, 접착제, 코팅을 포함하며 원료 경제성과 지속가능성 압력을 면밀히 살펴봅니다.</p><p>각 보고서는 세분화된 시장 규모와 전망에 더해 원자재 가격, 설비 증설, 규제 변화, 최종 수요를 분석합니다. 이러한 결과는 생산업체, 유통업체, 다운스트림 제조업체가 투자를 계획하고 포트폴리오를 최적화하며 순환경제와 배출 요건에 대응하도록 지원합니다.</p>',
                    ],
                    'zh' => [
                        'name' => '化学品与材料',
                        'meta_title' => '化学品与材料市场调研报告与行业分析',
                        'meta_description' => '本报告聚焦化学品与材料领域的市场调研，涵盖大宗化学品与特种化学品、石油化工、聚合物与塑料、先进材料与复合材料、胶粘剂及涂料。报告按地区提供市场规模、需求预测、原材料价格与产能变化、监管动态及主要企业竞争基准分析，助力生产商、分销商及下游制造商规划投资与优化产品组合，并系统梳理全球主要区域市场竞争格局，助力企业把握增长机遇。',
                        'description' => '<h2>化学品与材料市场调研与行业洞察</h2><p>化学品与材料产业支撑着从包装、建筑到电子、出行和医疗健康在内的几乎所有制造价值链。我们的研究涵盖大宗化学品与特种化学品、石油化工、聚合物与塑料、先进材料与复合材料、胶粘剂及涂料，并密切关注原料经济性与可持续发展压力。</p><p>每份报告在详细的市场规模与预测基础上，分析原材料价格、产能扩张、监管变化及终端需求。这些结论帮助生产商、分销商及下游制造商规划投资、优化产品组合，并应对循环经济与减排要求。</p>',
                    ],
                    'es' => [
                        'name' => 'Productos Químicos y Materiales',
                        'meta_title' => 'Informes de Investigación de Mercado Químicos y Materiales',
                        'meta_description' => 'Investigación de mercado de Productos Químicos y Materiales: químicos básicos y especializados, polímeros, materiales avanzados, con previsión de demanda y análisis competitivo por región.',
                        'description' => '<h2>Investigación de Mercado y Perspectivas del Sector de Productos Químicos y Materiales</h2><p>La industria química y de materiales sustenta prácticamente toda la cadena de valor manufacturera, desde el embalaje y la construcción hasta la electrónica, la movilidad y la salud. Nuestra cobertura incluye químicos básicos y especializados, petroquímicos, polímeros y plásticos, materiales avanzados y compuestos, adhesivos y recubrimientos, con especial atención a la economía de las materias primas y las presiones de sostenibilidad.</p><p>Los informes combinan un dimensionamiento detallado del mercado y previsiones con el análisis de precios de materias primas, ampliaciones de capacidad, cambios regulatorios y demanda de uso final. Estos hallazgos ayudan a productores, distribuidores y fabricantes downstream a planificar inversiones y optimizar sus carteras.</p>',
                    ],
                    'de' => [
                        'name' => 'Chemikalien und Materialien',
                        'meta_title' => 'Marktforschungsberichte Chemikalien und Materialien',
                        'meta_description' => 'Marktforschung zu Chemikalien und Materialien: Basis- und Spezialchemikalien, Polymere, Hochleistungswerkstoffe und Beschichtungen, mit Nachfrageprognosen und Wettbewerbs-Benchmarking nach Region.',
                        'description' => '<h2>Marktforschung und Brancheneinblicke Chemikalien und Materialien</h2><p>Die Chemie- und Materialindustrie bildet die Grundlage nahezu jeder Fertigungswertschöpfungskette, von Verpackung und Bauwesen bis hin zu Elektronik, Mobilität und Gesundheitswesen. Unsere Abdeckung umfasst Basis- und Spezialchemikalien, Petrochemikalien, Polymere und Kunststoffe, Hochleistungs- und Verbundwerkstoffe, Klebstoffe und Beschichtungen, mit besonderem Blick auf Rohstoffökonomie und Nachhaltigkeitsanforderungen.</p><p>Die Berichte verbinden eine detaillierte Marktgrößenschätzung und Prognosen mit einer Analyse von Rohstoffpreisen, Kapazitätserweiterungen, regulatorischen Änderungen und Endverbrauchernachfrage. Diese Erkenntnisse unterstützen Hersteller, Distributoren und nachgelagerte Fertigungsunternehmen bei Investitionsplanung und Portfoliooptimierung.</p>',
                    ],
                    'fr' => [
                        'name' => 'Produits Chimiques et Matériaux',
                        'meta_title' => 'Rapports d\'Études de Marché Produits Chimiques et Matériaux',
                        'meta_description' => 'Étude de marché sur les Produits Chimiques et Matériaux : chimie de base et de spécialité, polymères, matériaux avancés, avec prévisions de la demande et analyse concurrentielle par région.',
                        'description' => '<h2>Étude de Marché et Perspectives du Secteur des Produits Chimiques et Matériaux</h2><p>L\'industrie chimique et des matériaux sous-tend presque toutes les chaînes de valeur manufacturières, de l\'emballage et de la construction à l\'électronique, la mobilité et la santé. Notre couverture inclut la chimie de base et de spécialité, la pétrochimie, les polymères et plastiques, les matériaux avancés et composites, les adhésifs et les revêtements, avec une attention particulière à l\'économie des matières premières et aux pressions de durabilité.</p><p>Les rapports combinent un dimensionnement détaillé du marché et des prévisions avec l\'analyse des prix des matières premières, des extensions de capacité, des évolutions réglementaires et de la demande finale. Ces conclusions aident les producteurs, distributeurs et fabricants en aval à planifier leurs investissements et à optimiser leurs portefeuilles.</p>',
                    ],
                ],
            ],
            [
                'slug' => 'automotive',
                'translations' => [
                    'en' => [
                        'name' => 'Automotive',
                        'meta_title' => 'Automotive Market Research Reports & Industry Analysis',
                        'meta_description' => 'In-depth Automotive market research spanning electric and conventional vehicles, components, batteries and mobility services, with production forecasts and competitive intelligence by region.',
                        'description' => '<h2>Automotive Market Research and Industry Insights</h2><p>The automotive industry is navigating a once-in-a-century transition driven by electrification, software-defined vehicles, autonomous technology and shifting mobility models. Our research covers passenger cars and commercial vehicles, powertrains, components and semiconductors, batteries, aftermarket, and connected and shared mobility services.</p><p>Each report delivers vehicle production and sales forecasts alongside analysis of regulation, supply chain resilience, technology adoption curves and the competitive positioning of automakers and suppliers. These insights help manufacturers, tier suppliers and investors align product roadmaps and capital allocation with regional demand.</p>',
                    ],
                    'ja' => [
                        'name' => '自動車',
                        'meta_title' => '自動車の市場調査レポートと業界分析',
                        'meta_description' => '電動車・従来型車両、パワートレイン、部品・半導体、バッテリー、アフターマーケット、コネクテッドおよびシェアードモビリティを対象とする自動車分野の市場調査レポートです。生産・販売予測、規制やサプライチェーン、技術採用の動向、主要メーカーとサプライヤーの競合分析を地域別に提供し、製品戦略と投資判断を支援します。',
                        'description' => '<h2>自動車の市場調査と業界インサイト</h2><p>自動車産業は、電動化、ソフトウェア定義車両、自動運転技術、モビリティモデルの変化を背景に、100年に一度の転換期を迎えています。当社の調査は、乗用車・商用車、パワートレイン、部品・半導体、バッテリー、アフターマーケット、コネクテッドおよびシェアードモビリティサービスを網羅します。</p><p>各レポートは、車両生産・販売予測に加え、規制、サプライチェーンの強靭性、技術採用の推移、自動車メーカーとサプライヤーの競争ポジションを分析します。これにより、メーカー、ティアサプライヤー、投資家は製品ロードマップと資本配分を地域需要に整合させることができます。</p>',
                    ],
                    'ko' => [
                        'name' => '자동차',
                        'meta_title' => '자동차 시장 조사 보고서 및 산업 분석',
                        'meta_description' => '전기차·내연기관차, 파워트레인, 부품·반도체, 배터리, 애프터마켓, 커넥티드·공유 모빌리티를 아우르는 자동차 부문 시장 조사 보고서입니다. 생산·판매 전망, 규제와 공급망, 기술 채택 동향, 주요 완성차업체와 공급업체의 경쟁 분석을 지역별로 제공하여 제품 전략과 투자 판단을 지원합니다.',
                        'description' => '<h2>자동차 시장 조사 및 산업 인사이트</h2><p>자동차 산업은 전동화, 소프트웨어 정의 차량, 자율주행 기술, 모빌리티 모델의 변화에 힘입어 100년에 한 번 있는 전환기를 맞고 있습니다. 당사의 조사는 승용차와 상용차, 파워트레인, 부품·반도체, 배터리, 애프터마켓, 커넥티드 및 공유 모빌리티 서비스를 다룹니다.</p><p>각 보고서는 차량 생산·판매 전망과 함께 규제, 공급망 회복력, 기술 채택 곡선, 완성차업체와 공급업체의 경쟁 포지션을 분석합니다. 이를 통해 제조사, 티어 공급업체, 투자자는 제품 로드맵과 자본 배분을 지역 수요에 맞출 수 있습니다.</p>',
                    ],
                    'zh' => [
                        'name' => '汽车',
                        'meta_title' => '汽车市场调研报告与行业分析',
                        'meta_description' => '本报告聚焦汽车产业的市场调研，涵盖电动车与传统车辆、动力总成、零部件与半导体、电池、售后市场及网联和共享出行服务。报告按地区提供产量与销量预测、法规与供应链韧性分析、技术采用趋势及整车厂和供应商的竞争情报，助力制造商、一级供应商和投资者制定产品与投资战略，并系统梳理全球主要区域市场竞争格局，助力企业把握增长机遇。',
                        'description' => '<h2>汽车市场调研与行业洞察</h2><p>在电动化、软件定义汽车、自动驾驶技术及出行模式变革的推动下，汽车产业正经历百年一遇的转型。我们的研究涵盖乘用车与商用车、动力总成、零部件与半导体、电池、售后市场，以及网联和共享出行服务。</p><p>每份报告在提供车辆产销预测的同时，分析法规、供应链韧性、技术采用趋势，以及汽车制造商与供应商的竞争地位。这有助于制造商、一级供应商和投资者根据区域需求调整产品路线图和资本配置。</p>',
                    ],
                    'es' => [
                        'name' => 'Automoción',
                        'meta_title' => 'Informes de Investigación de Mercado de Automoción',
                        'meta_description' => 'Investigación de mercado del sector de Automoción: vehículos eléctricos y convencionales, componentes, baterías y movilidad, con previsiones de producción e inteligencia competitiva por región.',
                        'description' => '<h2>Investigación de Mercado y Perspectivas del Sector de Automoción</h2><p>La industria de automoción atraviesa una transición sin precedentes impulsada por la electrificación, los vehículos definidos por software, la tecnología autónoma y los nuevos modelos de movilidad. Nuestra investigación abarca turismos y vehículos comerciales, tren motriz, componentes y semiconductores, baterías, posventa y servicios de movilidad conectada y compartida.</p><p>Cada informe ofrece previsiones de producción y ventas de vehículos junto con el análisis de la regulación, la resiliencia de la cadena de suministro, las curvas de adopción tecnológica y el posicionamiento competitivo de fabricantes y proveedores. Estos datos ayudan a fabricantes, proveedores de nivel uno e inversores a alinear sus hojas de ruta con la demanda regional.</p>',
                    ],
                    'de' => [
                        'name' => 'Automobilindustrie',
                        'meta_title' => 'Marktforschungsberichte Automobilindustrie',
                        'meta_description' => 'Fundierte Marktforschung zur Automobilindustrie: Elektro- und konventionelle Fahrzeuge, Komponenten, Batterien und Mobilitätsdienste, mit Produktionsprognosen und Wettbewerbsanalyse nach Region.',
                        'description' => '<h2>Marktforschung und Brancheneinblicke Automobilindustrie</h2><p>Die Automobilindustrie durchläuft einen historischen Wandel, angetrieben durch Elektrifizierung, softwaredefinierte Fahrzeuge, autonome Technologien und neue Mobilitätsmodelle. Unsere Forschung umfasst Personen- und Nutzfahrzeuge, Antriebsstränge, Komponenten und Halbleiter, Batterien, Aftermarket sowie vernetzte und geteilte Mobilitätsdienste.</p><p>Jeder Bericht liefert Produktions- und Absatzprognosen sowie eine Analyse von Regulierung, Lieferkettenresilienz, Technologieakzeptanz und der Wettbewerbsposition von Herstellern und Zulieferern. Diese Erkenntnisse helfen Herstellern, Zulieferern und Investoren, Produktstrategien und Kapitalallokation an die regionale Nachfrage anzupassen.</p>',
                    ],
                    'fr' => [
                        'name' => 'Automobile',
                        'meta_title' => 'Rapports d\'Études de Marché Automobile',
                        'meta_description' => 'Étude de marché du secteur Automobile : véhicules électriques et conventionnels, composants, batteries et mobilité, avec prévisions de production et intelligence concurrentielle par région.',
                        'description' => '<h2>Étude de Marché et Perspectives du Secteur Automobile</h2><p>L\'industrie automobile traverse une transition sans précédent, portée par l\'électrification, les véhicules définis par logiciel, la technologie autonome et l\'évolution des modèles de mobilité. Notre étude couvre les véhicules particuliers et utilitaires, les groupes motopropulseurs, les composants et semi-conducteurs, les batteries, l\'après-vente et les services de mobilité connectée et partagée.</p><p>Chaque rapport propose des prévisions de production et de ventes, ainsi qu\'une analyse de la réglementation, de la résilience de la chaîne d\'approvisionnement, des courbes d\'adoption technologique et du positionnement concurrentiel des constructeurs et équipementiers. Ces analyses aident constructeurs, équipementiers et investisseurs à aligner leurs feuilles de route sur la demande régionale.</p>',
                    ],
                ],
            ],
            [
                'slug' => 'aerospace-and-defense',
                'translations' => [
                    'en' => [
                        'name' => 'Aerospace and Defense',
                        'meta_title' => 'Aerospace and Defense Market Research & Analysis',
                        'meta_description' => 'Aerospace and Defense market research covering commercial aviation, military platforms, space systems, MRO and defense electronics, with spending forecasts and program-level competitive analysis.',
                        'description' => '<h2>Aerospace and Defense Market Research and Industry Insights</h2><p>Aerospace and defense demand is shaped by air travel recovery, fleet renewal, rising defense budgets and rapid growth in commercial space activity. Our research spans commercial and business aviation, military aircraft and land and naval systems, missiles, satellites and launch services, avionics, and maintenance, repair and overhaul.</p><p>Reports combine platform and program forecasts with analysis of procurement priorities, geopolitical drivers, supply chain capacity and the strategies of primes and subsystem suppliers. The insights support contractors, component makers and investors in tracking budget cycles, entering new programs and assessing long-cycle risk.</p>',
                    ],
                    'ja' => [
                        'name' => '航空宇宙・防衛',
                        'meta_title' => '航空宇宙・防衛の市場調査レポートと業界分析',
                        'meta_description' => '民間・ビジネス航空、軍用機、陸上・海上システム、ミサイル、衛星・打ち上げサービス、アビオニクス、MROを対象とする航空宇宙・防衛分野の市場調査レポートです。プログラム別の予測、調達の優先順位、地政学的要因、サプライチェーン能力、主要企業の競合分析を提供し、予算サイクルの把握と参入・投資判断を支援します。',
                        'description' => '<h2>航空宇宙・防衛の市場調査と業界インサイト</h2><p>航空宇宙・防衛の需要は、航空旅行の回復、機材更新、防衛予算の増加、商業宇宙活動の急成長によって形成されています。当社の調査は、民間およびビジネス航空、軍用機・陸上/海上システム、ミサイル、衛星・打ち上げサービス、アビオニクス、整備・修理・オーバーホールを網羅します。</p><p>各レポートは、プラットフォームおよびプログラム予測に加え、調達の優先順位、地政学的要因、サプライチェーン能力、プライムおよびサブシステムサプライヤーの戦略を分析します。これにより、請負業者、部品メーカー、投資家は予算サイクルの把握、新規プログラムへの参入、長期リスクの評価を行えます。</p>',
                    ],
                    'ko' => [
                        'name' => '항공우주 및 방위',
                        'meta_title' => '항공우주 및 방위 시장 조사 보고서 및 산업 분석',
                        'meta_description' => '민간·비즈니스 항공, 군용기, 지상·해상 체계, 미사일, 위성·발사 서비스, 항공전자, MRO를 아우르는 항공우주 및 방위 부문 시장 조사 보고서입니다. 프로그램별 전망, 조달 우선순위, 지정학적 요인, 공급망 역량, 주요 기업의 경쟁 분석을 제공하여 예산 주기 파악과 진입·투자 판단을 지원합니다.',
                        'description' => '<h2>항공우주 및 방위 시장 조사 및 산업 인사이트</h2><p>항공우주 및 방위 수요는 항공 여행 회복, 기단 교체, 국방 예산 증가, 상업 우주 활동의 급성장에 따라 형성됩니다. 당사의 조사는 민간 및 비즈니스 항공, 군용기와 지상·해상 체계, 미사일, 위성·발사 서비스, 항공전자, 정비·수리·창정비를 다룹니다.</p><p>각 보고서는 플랫폼 및 프로그램 전망과 함께 조달 우선순위, 지정학적 요인, 공급망 역량, 주계약업체와 하위체계 공급업체의 전략을 분석합니다. 이를 통해 계약업체, 부품 제조사, 투자자는 예산 주기를 추적하고 신규 프로그램에 진입하며 장기 리스크를 평가할 수 있습니다.</p>',
                    ],
                    'zh' => [
                        'name' => '航空航天与国防',
                        'meta_title' => '航空航天与国防市场调研报告与行业分析',
                        'meta_description' => '本报告聚焦航空航天与国防领域的市场调研，涵盖民用与公务航空、军用飞机及陆海系统、导弹、卫星与发射服务、航电设备及维修维护。报告按项目提供支出预测、采购动态、地缘政治因素及供应链产能分析，以及主承包商与分包商的竞争分析，助力企业把握预算周期与投资时机，并系统梳理全球主要区域市场竞争格局，助力企业把握增长机遇。',
                        'description' => '<h2>航空航天与国防市场调研与行业洞察</h2><p>航空旅行复苏、机队更新、国防预算增长以及商业航天活动的快速发展共同塑造了航空航天与国防需求。我们的研究涵盖民用与公务航空、军用飞机及陆海系统、导弹、卫星与发射服务、航电设备，以及维修、维护和大修。</p><p>每份报告在提供平台与项目预测的同时，分析采购优先级、地缘政治因素、供应链产能，以及主承包商与分系统供应商的战略。这有助于承包商、零部件制造商和投资者把握预算周期、进入新项目并评估长周期风险。</p>',
                    ],
                    'es' => [
                        'name' => 'Aeroespacial y Defensa',
                        'meta_title' => 'Informes de Investigación de Mercado Aeroespacial y Defensa',
                        'meta_description' => 'Investigación de mercado Aeroespacial y de Defensa: aviación comercial, plataformas militares, sistemas espaciales, MRO y electrónica, con previsiones de gasto y análisis competitivo por programa.',
                        'description' => '<h2>Investigación de Mercado y Perspectivas del Sector Aeroespacial y de Defensa</h2><p>La demanda aeroespacial y de defensa está determinada por la recuperación del transporte aéreo, la renovación de flotas, el aumento de los presupuestos de defensa y el rápido crecimiento de la actividad espacial comercial. Nuestra investigación abarca la aviación comercial y de negocios, aeronaves militares y sistemas terrestres y navales, misiles, satélites y servicios de lanzamiento, aviónica, y mantenimiento, reparación y revisión.</p><p>Los informes combinan previsiones de plataformas y programas con el análisis de las prioridades de adquisición, los factores geopolíticos, la capacidad de la cadena de suministro y las estrategias de los contratistas principales y proveedores de subsistemas. Estos datos ayudan a contratistas, fabricantes de componentes e inversores a seguir los ciclos presupuestarios.</p>',
                    ],
                    'de' => [
                        'name' => 'Luft- und Raumfahrt sowie Verteidigung',
                        'meta_title' => 'Marktforschungsberichte Luft- und Raumfahrt sowie Verteidigung',
                        'meta_description' => 'Marktforschung zu Luft- und Raumfahrt sowie Verteidigung: zivile Luftfahrt, Militärplattformen, Raumfahrtsysteme und Verteidigungselektronik, mit Ausgabenprognosen und Wettbewerbsanalyse.',
                        'description' => '<h2>Marktforschung und Brancheneinblicke Luft- und Raumfahrt sowie Verteidigung</h2><p>Die Nachfrage in Luft- und Raumfahrt sowie Verteidigung wird durch die Erholung des Luftverkehrs, Flottenerneuerung, steigende Verteidigungsbudgets und das schnelle Wachstum kommerzieller Raumfahrtaktivitäten geprägt. Unsere Forschung umfasst die zivile und Geschäftsluftfahrt, Militärflugzeuge sowie Land- und Marinesysteme, Raketen, Satelliten und Startdienste, Avionik sowie Wartung, Reparatur und Überholung.</p><p>Die Berichte verbinden Plattform- und Programmprognosen mit einer Analyse von Beschaffungsprioritäten, geopolitischen Treibern, Lieferkettenkapazität sowie den Strategien von Hauptauftragnehmern und Subsystemlieferanten. Diese Erkenntnisse unterstützen Auftragnehmer, Komponentenhersteller und Investoren bei der Budgetplanung.</p>',
                    ],
                    'fr' => [
                        'name' => 'Aérospatiale et Défense',
                        'meta_title' => 'Rapports d\'Études de Marché Aérospatiale et Défense',
                        'meta_description' => 'Étude de marché Aérospatiale et Défense : aviation commerciale, plateformes militaires, systèmes spatiaux, MRO et électronique, avec prévisions de dépenses et analyse concurrentielle par programme.',
                        'description' => '<h2>Étude de Marché et Perspectives du Secteur Aérospatial et de la Défense</h2><p>La demande aérospatiale et de défense est façonnée par la reprise du transport aérien, le renouvellement des flottes, la hausse des budgets de défense et la croissance rapide de l\'activité spatiale commerciale. Notre étude couvre l\'aviation commerciale et d\'affaires, les aéronefs militaires et les systèmes terrestres et navals, les missiles, les satellites et services de lancement, l\'avionique, ainsi que la maintenance et les révisions.</p><p>Les rapports combinent des prévisions par plateforme et programme avec une analyse des priorités d\'acquisition, des facteurs géopolitiques, de la capacité de la chaîne d\'approvisionnement et des stratégies des maîtres d\'œuvre et fournisseurs de sous-systèmes. Ces analyses aident les entrepreneurs, fabricants de composants et investisseurs à suivre les cycles budgétaires.</p>',
                    ],
                ],
            ],
            [
                'slug' => 'industrial-machinery',
                'translations' => [
                    'en' => [
                        'name' => 'Industrial Machinery',
                        'meta_title' => 'Industrial Machinery Market Research & Industry Analysis',
                        'meta_description' => 'Industrial Machinery market research across process equipment, machine tools, automation, robotics, pumps and compressors, with demand forecasts and competitive benchmarking by region and end use.',
                        'description' => '<h2>Industrial Machinery Market Research and Industry Insights</h2><p>Industrial machinery is central to productivity across manufacturing, energy, construction and logistics, and demand tracks closely with capital investment cycles and automation adoption. Our coverage includes process and packaging equipment, machine tools, pumps, compressors and valves, material handling, robotics and factory automation systems.</p><p>Each report pairs equipment market sizing and forecasts with analysis of industrial output trends, reshoring and capacity expansion, digitalization and the competitive landscape of OEMs and component suppliers. These findings help machinery builders, distributors and investors target growth segments and plan production and channel strategy.</p>',
                    ],
                    'ja' => [
                        'name' => '産業機械',
                        'meta_title' => '産業機械の市場調査レポートと業界分析',
                        'meta_description' => 'プロセス機器・包装機器、工作機械、ポンプ・圧縮機・バルブ、マテリアルハンドリング、ロボット、工場自動化を対象とする産業機械分野の市場調査レポートです。設備需要の予測、設備投資サイクル、生産回帰と能力増強、デジタル化、OEMと部品サプライヤーの競合分析を地域・用途別に提供し、成長分野の特定と戦略策定を支援します。',
                        'description' => '<h2>産業機械の市場調査と業界インサイト</h2><p>産業機械は、製造、エネルギー、建設、物流にわたる生産性の中核であり、需要は設備投資サイクルと自動化の採用に密接に連動します。当社の調査対象には、プロセス機器・包装機器、工作機械、ポンプ・圧縮機・バルブ、マテリアルハンドリング、ロボット、工場自動化システムが含まれます。</p><p>各レポートは、機器の市場規模と予測に加え、鉱工業生産の動向、生産回帰と能力増強、デジタル化、OEMおよび部品サプライヤーの競争環境を分析します。これにより、機械メーカー、流通業者、投資家は成長セグメントを特定し、生産およびチャネル戦略を計画できます。</p>',
                    ],
                    'ko' => [
                        'name' => '산업 기계',
                        'meta_title' => '산업 기계 시장 조사 보고서 및 산업 분석',
                        'meta_description' => '공정·포장 장비, 공작기계, 펌프·압축기·밸브, 물류 취급 장비, 로봇, 공장 자동화를 아우르는 산업 기계 부문 시장 조사 보고서입니다. 설비 수요 전망, 설비 투자 주기, 리쇼어링과 생산능력 확장, 디지털화, OEM과 부품 공급업체의 경쟁 분석을 지역·용도별로 제공하여 성장 분야 발굴과 전략 수립을 지원합니다.',
                        'description' => '<h2>산업 기계 시장 조사 및 산업 인사이트</h2><p>산업 기계는 제조, 에너지, 건설, 물류 전반의 생산성에 핵심이며, 수요는 설비 투자 주기와 자동화 채택에 밀접하게 연동됩니다. 당사의 조사 범위에는 공정 및 포장 장비, 공작기계, 펌프·압축기·밸브, 물류 취급, 로봇, 공장 자동화 시스템이 포함됩니다.</p><p>각 보고서는 장비 시장 규모와 전망에 더해 산업 생산 추세, 리쇼어링과 생산능력 확장, 디지털화, OEM 및 부품 공급업체의 경쟁 구도를 분석합니다. 이러한 결과는 기계 제조사, 유통업체, 투자자가 성장 부문을 겨냥하고 생산 및 채널 전략을 수립하도록 지원합니다.</p>',
                    ],
                    'zh' => [
                        'name' => '工业机械',
                        'meta_title' => '工业机械市场调研报告与行业分析',
                        'meta_description' => '本报告聚焦工业机械领域的市场调研，涵盖工艺与包装设备、机床、泵阀压缩机、物料搬运、机器人及工厂自动化系统。报告按地区和用途提供设备需求预测、资本投资趋势、产能回流与数字化进程分析，以及主要企业与零部件供应商的竞争基准，助力机械制造商锁定增长细分领域，并系统梳理全球主要区域市场竞争格局，助力企业把握增长机遇。',
                        'description' => '<h2>工业机械市场调研与行业洞察</h2><p>工业机械是制造、能源、建筑及物流领域生产力的核心，其需求与资本投资周期及自动化应用密切相关。我们的研究范围包括工艺与包装设备、机床、泵阀压缩机、物料搬运、机器人及工厂自动化系统。</p><p>每份报告在设备市场规模与预测基础上，分析工业产出趋势、产能回流与扩张、数字化进程，以及原始设备制造商与零部件供应商的竞争格局。这有助于机械制造商、分销商和投资者锁定增长细分领域并规划生产与渠道战略。</p>',
                    ],
                    'es' => [
                        'name' => 'Maquinaria Industrial',
                        'meta_title' => 'Informes de Investigación de Mercado de Maquinaria Industrial',
                        'meta_description' => 'Investigación de mercado de Maquinaria Industrial: equipos de proceso, máquinas herramienta, automatización, robótica, bombas y compresores, con previsiones y benchmarking competitivo por región.',
                        'description' => '<h2>Investigación de Mercado y Perspectivas del Sector de Maquinaria Industrial</h2><p>La maquinaria industrial es fundamental para la productividad en la fabricación, la energía, la construcción y la logística, y su demanda está estrechamente ligada a los ciclos de inversión de capital y a la adopción de la automatización. Nuestra cobertura incluye equipos de proceso y embalaje, máquinas herramienta, bombas, compresores y válvulas, manipulación de materiales, robótica y sistemas de automatización de fábricas.</p><p>Cada informe combina el dimensionamiento y las previsiones del mercado de equipos con el análisis de las tendencias de producción industrial, la relocalización y expansión de capacidad, la digitalización y el panorama competitivo de fabricantes y proveedores. Estos hallazgos ayudan a constructores de maquinaria, distribuidores e inversores.</p>',
                    ],
                    'de' => [
                        'name' => 'Industriemaschinen',
                        'meta_title' => 'Marktforschungsberichte Industriemaschinen',
                        'meta_description' => 'Marktforschung zu Industriemaschinen: Prozessanlagen, Werkzeugmaschinen, Automatisierung, Robotik, Pumpen und Kompressoren, mit Nachfrageprognosen und Wettbewerbs-Benchmarking nach Region.',
                        'description' => '<h2>Marktforschung und Brancheneinblicke Industriemaschinen</h2><p>Industriemaschinen sind zentral für die Produktivität in Fertigung, Energie, Bauwesen und Logistik, wobei die Nachfrage eng mit Investitionszyklen und der Einführung von Automatisierung zusammenhängt. Unsere Abdeckung umfasst Prozess- und Verpackungsanlagen, Werkzeugmaschinen, Pumpen, Kompressoren und Ventile, Materialtransport, Robotik und Fabrikautomatisierungssysteme.</p><p>Jeder Bericht verbindet Marktgrößenschätzungen und Prognosen für Anlagen mit einer Analyse von Trends der Industrieproduktion, Reshoring und Kapazitätserweiterung, Digitalisierung sowie der Wettbewerbslandschaft von OEMs und Zulieferern. Diese Erkenntnisse helfen Maschinenbauern, Distributoren und Investoren, Wachstumssegmente zu identifizieren.</p>',
                    ],
                    'fr' => [
                        'name' => 'Machines Industrielles',
                        'meta_title' => 'Rapports d\'Études de Marché Machines Industrielles',
                        'meta_description' => 'Étude de marché des Machines Industrielles : équipements de procédé, machines-outils, automatisation, robotique, pompes et compresseurs, avec prévisions et analyse comparative par région.',
                        'description' => '<h2>Étude de Marché et Perspectives du Secteur des Machines Industrielles</h2><p>Les machines industrielles sont essentielles à la productivité dans l\'industrie manufacturière, l\'énergie, la construction et la logistique, la demande suivant étroitement les cycles d\'investissement et l\'adoption de l\'automatisation. Notre couverture comprend les équipements de procédé et d\'emballage, les machines-outils, les pompes, compresseurs et vannes, la manutention, la robotique et les systèmes d\'automatisation d\'usine.</p><p>Chaque rapport associe le dimensionnement du marché des équipements et les prévisions à une analyse des tendances de production industrielle, de la relocalisation et de l\'expansion des capacités, de la digitalisation et du paysage concurrentiel des OEM et fournisseurs. Ces résultats aident les fabricants de machines, distributeurs et investisseurs.</p>',
                    ],
                ],
            ],
            [
                'slug' => 'semiconductors-and-electronics',
                'translations' => [
                    'en' => [
                        'name' => 'Semiconductors and Electronics',
                        'meta_title' => 'Semiconductors and Electronics Market Research & Analysis',
                        'meta_description' => 'Semiconductors and Electronics market research covering chips, foundry and packaging, electronic components, displays and PCBs, with demand forecasts and competitive analysis across the supply chain.',
                        'description' => '<h2>Semiconductors and Electronics Market Research and Industry Insights</h2><p>Semiconductors and electronics sit at the core of digital transformation, powering computing, communications, automotive, industrial and consumer applications. Our research spans logic, memory, analog and power devices, foundry and advanced packaging, semiconductor equipment and materials, passive and active components, displays and printed circuit boards.</p><p>Reports combine device and application market sizing with analysis of capacity investment, technology node transitions, export controls and supply chain localization, and the strategies of chipmakers, equipment vendors and OEMs. The insights help suppliers, buyers and investors anticipate cycles and prioritize design wins and capacity.</p>',
                    ],
                    'ja' => [
                        'name' => '半導体・エレクトロニクス',
                        'meta_title' => '半導体・エレクトロニクスの市場調査レポートと業界分析',
                        'meta_description' => 'ロジック・メモリ・アナログ・パワー半導体、ファウンドリと先端パッケージング、製造装置・材料、電子部品、ディスプレイ、プリント基板を対象とする半導体・エレクトロニクス分野の市場調査レポートです。需要予測、能力投資、微細化ノードの移行、輸出規制、主要企業の競合分析をサプライチェーン全体で提供し、投資判断とデザインウィンの優先順位付けを支援します。',
                        'description' => '<h2>半導体・エレクトロニクスの市場調査と業界インサイト</h2><p>半導体・エレクトロニクスはデジタルトランスフォーメーションの中核であり、コンピューティング、通信、自動車、産業、民生の各用途を支えています。当社の調査は、ロジック、メモリ、アナログ・パワーデバイス、ファウンドリと先端パッケージング、半導体製造装置・材料、受動・能動部品、ディスプレイ、プリント基板を網羅します。</p><p>各レポートは、デバイスおよび用途別の市場規模に加え、能力投資、微細化ノードの移行、輸出規制とサプライチェーンの現地化、チップメーカー・装置ベンダー・OEMの戦略を分析します。これにより、サプライヤー、購買側、投資家はサイクルを予測し、デザインウィンと生産能力の優先順位を付けられます。</p>',
                    ],
                    'ko' => [
                        'name' => '반도체 및 전자',
                        'meta_title' => '반도체 및 전자 시장 조사 보고서 및 산업 분석',
                        'meta_description' => '로직·메모리·아날로그·전력 반도체, 파운드리와 첨단 패키징, 제조 장비·소재, 전자 부품, 디스플레이, 인쇄회로기판을 아우르는 반도체 및 전자 부문 시장 조사 보고서입니다. 수요 전망, 생산능력 투자, 공정 노드 전환, 수출 규제, 주요 기업의 경쟁 분석을 공급망 전반에서 제공하여 투자 판단과 디자인 윈 우선순위 설정을 지원합니다.',
                        'description' => '<h2>반도체 및 전자 시장 조사 및 산업 인사이트</h2><p>반도체와 전자는 디지털 전환의 핵심으로 컴퓨팅, 통신, 자동차, 산업, 소비자 응용을 구동합니다. 당사의 조사는 로직, 메모리, 아날로그·전력 소자, 파운드리와 첨단 패키징, 반도체 장비·소재, 수동·능동 부품, 디스플레이, 인쇄회로기판을 다룹니다.</p><p>각 보고서는 소자 및 응용별 시장 규모와 함께 생산능력 투자, 공정 노드 전환, 수출 규제와 공급망 현지화, 칩 제조사·장비 업체·OEM의 전략을 분석합니다. 이를 통해 공급업체, 구매자, 투자자는 주기를 예측하고 디자인 윈과 생산능력의 우선순위를 정할 수 있습니다.</p>',
                    ],
                    'zh' => [
                        'name' => '半导体与电子',
                        'meta_title' => '半导体与电子市场调研报告与行业分析',
                        'meta_description' => '本报告聚焦半导体与电子领域的市场调研，涵盖逻辑芯片、存储器、模拟与功率器件、晶圆代工与先进封装、电子元器件、显示面板及印刷电路板。报告提供需求预测及贯穿整个供应链的竞争分析，涵盖产能投资、制程节点迁移与出口管制等关键议题，助力供应商与投资者预判周期，并系统梳理全球主要区域市场竞争格局，助力企业把握增长机遇。',
                        'description' => '<h2>半导体与电子市场调研与行业洞察</h2><p>半导体与电子产业是数字化转型的核心，为计算、通信、汽车、工业及消费领域提供动力。我们的研究涵盖逻辑芯片、存储器、模拟与功率器件、晶圆代工与先进封装、半导体设备与材料、无源与有源元器件、显示面板及印刷电路板。</p><p>每份报告在器件与应用市场规模基础上，分析产能投资、制程节点迁移、出口管制与供应链本地化，以及芯片制造商、设备供应商与整机厂商的战略。这有助于供应商、买家和投资者预判周期并确定设计导入与产能的优先级。</p>',
                    ],
                    'es' => [
                        'name' => 'Semiconductores y Electrónica',
                        'meta_title' => 'Informes de Investigación de Mercado de Semiconductores y Electrónica',
                        'meta_description' => 'Investigación de mercado de Semiconductores y Electrónica: chips, fundición y encapsulado, componentes electrónicos, pantallas y PCB, con previsiones de demanda y análisis competitivo por región.',
                        'description' => '<h2>Investigación de Mercado y Perspectivas del Sector de Semiconductores y Electrónica</h2><p>Los semiconductores y la electrónica son el núcleo de la transformación digital, impulsando aplicaciones informáticas, de comunicaciones, automoción, industriales y de consumo. Nuestra investigación abarca dispositivos lógicos, de memoria, analógicos y de potencia, fundición y encapsulado avanzado, equipos y materiales de semiconductores, componentes pasivos y activos, pantallas y placas de circuito impreso.</p><p>Los informes combinan el dimensionamiento del mercado por dispositivo y aplicación con el análisis de la inversión en capacidad, las transiciones de nodos tecnológicos, los controles de exportación y las estrategias de fabricantes de chips, proveedores de equipos y fabricantes de equipos originales.</p>',
                    ],
                    'de' => [
                        'name' => 'Halbleiter und Elektronik',
                        'meta_title' => 'Marktforschungsberichte Halbleiter und Elektronik',
                        'meta_description' => 'Marktforschung zu Halbleitern und Elektronik: Chips, Foundry und Packaging, elektronische Komponenten, Displays und Leiterplatten, mit Nachfrageprognosen und Wettbewerbsanalyse in der Lieferkette.',
                        'description' => '<h2>Marktforschung und Brancheneinblicke Halbleiter und Elektronik</h2><p>Halbleiter und Elektronik bilden den Kern der digitalen Transformation und treiben Anwendungen in Computing, Kommunikation, Automobil, Industrie und Konsumgütern an. Unsere Forschung umfasst Logik-, Speicher-, Analog- und Leistungsbauelemente, Foundry und fortschrittliches Packaging, Halbleiterausrüstung und -materialien, passive und aktive Komponenten, Displays und Leiterplatten.</p><p>Die Berichte verbinden Marktgrößenschätzungen nach Bauelement und Anwendung mit einer Analyse von Kapazitätsinvestitionen, Technologie-Node-Übergängen, Exportkontrollen und den Strategien von Chipherstellern, Ausrüstungsanbietern und OEMs.</p>',
                    ],
                    'fr' => [
                        'name' => 'Semi-conducteurs et Électronique',
                        'meta_title' => 'Rapports d\'Études de Marché Semi-conducteurs et Électronique',
                        'meta_description' => 'Étude de marché des Semi-conducteurs et de l\'Électronique : puces, fonderie et packaging, composants électroniques, écrans et PCB, avec prévisions de la demande et analyse concurrentielle par région.',
                        'description' => '<h2>Étude de Marché et Perspectives du Secteur des Semi-conducteurs et de l\'Électronique</h2><p>Les semi-conducteurs et l\'électronique sont au cœur de la transformation numérique, alimentant les applications informatiques, de communication, automobiles, industrielles et grand public. Notre étude couvre les dispositifs logiques, mémoire, analogiques et de puissance, la fonderie et le packaging avancé, les équipements et matériaux pour semi-conducteurs, les composants passifs et actifs, les écrans et les circuits imprimés.</p><p>Les rapports combinent le dimensionnement du marché par dispositif et application avec une analyse des investissements en capacité, des transitions de nœuds technologiques, des contrôles à l\'exportation et des stratégies des fabricants de puces, équipementiers et OEM.</p>',
                    ],
                ],
            ],
            [
                'slug' => 'food-and-beverages',
                'translations' => [
                    'en' => [
                        'name' => 'Food and Beverages',
                        'meta_title' => 'Food and Beverages Market Research Reports & Analysis',
                        'meta_description' => 'Food and Beverages market research across packaged foods, beverages, dairy and alternatives, ingredients and foodservice, with consumption forecasts and brand-level competitive analysis by region.',
                        'description' => '<h2>Food and Beverages Market Research and Industry Insights</h2><p>The food and beverage industry is being reshaped by health and wellness trends, plant-based and functional products, premiumization and evolving retail and delivery channels. Our coverage spans packaged and fresh foods, alcoholic and non-alcoholic beverages, dairy and alternatives, ingredients and additives, nutrition and foodservice.</p><p>Each report combines category market sizing and forecasts with analysis of consumer behavior, pricing and input costs, regulation and labeling, and the competitive dynamics of manufacturers, private label and challenger brands. These insights help producers, ingredient suppliers and retailers refine portfolios and channel strategy.</p>',
                    ],
                    'ja' => [
                        'name' => '食品・飲料',
                        'meta_title' => '食品・飲料の市場調査レポートと業界分析',
                        'meta_description' => '加工食品・生鮮食品、アルコール・ノンアルコール飲料、乳製品と代替品、原料・添加物、栄養、フードサービスを対象とする食品・飲料分野の市場調査レポートです。消費予測、消費者行動、価格と投入コスト、規制と表示、メーカー・小売・チャレンジャーブランドの競合分析を地域別に提供し、ポートフォリオとチャネル戦略の策定と投資判断を支援します。',
                        'description' => '<h2>食品・飲料の市場調査と業界インサイト</h2><p>食品・飲料産業は、健康・ウェルネスの潮流、植物由来および機能性製品、プレミアム化、進化する小売・デリバリーチャネルによって再形成されています。当社の調査対象は、加工食品・生鮮食品、アルコール・ノンアルコール飲料、乳製品と代替品、原料・添加物、栄養、フードサービスに及びます。</p><p>各レポートは、カテゴリー別の市場規模と予測に加え、消費者行動、価格と投入コスト、規制と表示、メーカー・プライベートブランド・チャレンジャーブランドの競争動態を分析します。これにより、生産者、原料サプライヤー、小売業者はポートフォリオとチャネル戦略を精緻化できます。</p>',
                    ],
                    'ko' => [
                        'name' => '식품 및 음료',
                        'meta_title' => '식품 및 음료 시장 조사 보고서 및 산업 분석',
                        'meta_description' => '가공식품·신선식품, 주류·비주류 음료, 유제품과 대체품, 원료·첨가물, 영양, 외식 서비스를 아우르는 식품 및 음료 부문 시장 조사 보고서입니다. 소비 전망, 소비자 행동, 가격과 투입 비용, 규제와 라벨링, 제조사와 브랜드의 경쟁 분석을 지역별로 제공하여 포트폴리오와 채널 전략 수립을 지원합니다.',
                        'description' => '<h2>식품 및 음료 시장 조사 및 산업 인사이트</h2><p>식품 및 음료 산업은 건강·웰빙 트렌드, 식물성 및 기능성 제품, 프리미엄화, 변화하는 소매·배송 채널로 재편되고 있습니다. 당사의 조사 범위는 가공식품과 신선식품, 주류 및 비주류 음료, 유제품과 대체품, 원료·첨가물, 영양, 외식 서비스에 이릅니다.</p><p>각 보고서는 카테고리별 시장 규모와 전망에 더해 소비자 행동, 가격과 투입 비용, 규제와 라벨링, 제조사·자체 브랜드·도전 브랜드의 경쟁 역학을 분석합니다. 이를 통해 생산자, 원료 공급업체, 소매업체는 포트폴리오와 채널 전략을 다듬을 수 있습니다.</p>',
                    ],
                    'zh' => [
                        'name' => '食品与饮料',
                        'meta_title' => '食品与饮料市场调研报告与行业分析',
                        'meta_description' => '本报告聚焦食品与饮料领域的市场调研，涵盖包装食品与生鲜食品、含酒精与不含酒精饮料、乳制品及替代品、原料添加剂、营养及餐饮服务。报告按地区提供消费预测、消费者行为、价格与成本分析，以及品牌层面的竞争分析，助力生产商、原料供应商和零售商优化产品组合与渠道战略，并系统梳理全球主要区域市场竞争格局，助力企业把握增长机遇。',
                        'description' => '<h2>食品与饮料市场调研与行业洞察</h2><p>健康与养生趋势、植物基与功能性产品、高端化以及零售和配送渠道的演变正在重塑食品饮料行业。我们的研究涵盖包装食品与生鲜食品、含酒精与不含酒精饮料、乳制品与替代品、原料及添加剂、营养及餐饮服务。</p><p>每份报告在分类别市场规模与预测基础上，分析消费者行为、价格与投入成本、监管与标签要求，以及制造商、自有品牌与挑战者品牌的竞争动态。这有助于生产商、原料供应商和零售商优化产品组合与渠道战略。</p>',
                    ],
                    'es' => [
                        'name' => 'Alimentación y Bebidas',
                        'meta_title' => 'Informes de Investigación de Mercado de Alimentación y Bebidas',
                        'meta_description' => 'Investigación de mercado de Alimentación y Bebidas: alimentos envasados, bebidas, lácteos, ingredientes y foodservice, con previsiones de consumo y análisis competitivo de marcas por región.',
                        'description' => '<h2>Investigación de Mercado y Perspectivas del Sector de Alimentación y Bebidas</h2><p>La industria de alimentación y bebidas está siendo transformada por las tendencias de salud y bienestar, los productos de origen vegetal y funcionales, la premiumización y la evolución de los canales de retail y entrega. Nuestra cobertura abarca alimentos envasados y frescos, bebidas alcohólicas y no alcohólicas, lácteos y alternativas, ingredientes y aditivos, nutrición y foodservice.</p><p>Cada informe combina el dimensionamiento y las previsiones por categoría con el análisis del comportamiento del consumidor, los precios y costes de los insumos, la regulación y el etiquetado, y la dinámica competitiva entre fabricantes, marcas blancas y marcas retadoras. Estos datos ayudan a productores y minoristas a perfeccionar su cartera.</p>',
                    ],
                    'de' => [
                        'name' => 'Lebensmittel und Getränke',
                        'meta_title' => 'Marktforschungsberichte Lebensmittel und Getränke',
                        'meta_description' => 'Marktforschung zu Lebensmitteln und Getränken: verpackte Lebensmittel, Getränke, Molkereiprodukte, Zutaten und Gastronomie, mit Konsumprognosen und markenbezogener Wettbewerbsanalyse nach Region.',
                        'description' => '<h2>Marktforschung und Brancheneinblicke Lebensmittel und Getränke</h2><p>Die Lebensmittel- und Getränkeindustrie wird durch Gesundheits- und Wellnesstrends, pflanzliche und funktionale Produkte, Premiumisierung sowie sich wandelnde Einzelhandels- und Lieferkanäle neu geprägt. Unsere Abdeckung umfasst verpackte und frische Lebensmittel, alkoholische und alkoholfreie Getränke, Molkereiprodukte und Alternativen, Zutaten und Zusatzstoffe, Ernährung und Gastronomie.</p><p>Jeder Bericht verbindet Marktgrößenschätzungen und Prognosen nach Kategorie mit einer Analyse des Verbraucherverhaltens, der Preise und Inputkosten, der Regulierung und Kennzeichnung sowie der Wettbewerbsdynamik zwischen Herstellern, Eigenmarken und Herausforderern.</p>',
                    ],
                    'fr' => [
                        'name' => 'Alimentation et Boissons',
                        'meta_title' => 'Rapports d\'Études de Marché Alimentation et Boissons',
                        'meta_description' => 'Étude de marché Alimentation et Boissons : produits emballés, boissons, produits laitiers, ingrédients et restauration, avec prévisions de consommation et analyse des marques par région.',
                        'description' => '<h2>Étude de Marché et Perspectives du Secteur de l\'Alimentation et des Boissons</h2><p>L\'industrie de l\'alimentation et des boissons est transformée par les tendances de santé et de bien-être, les produits d\'origine végétale et fonctionnels, la premiumisation et l\'évolution des canaux de distribution et de livraison. Notre couverture comprend les aliments emballés et frais, les boissons alcoolisées et non alcoolisées, les produits laitiers et alternatives, les ingrédients et additifs, la nutrition et la restauration.</p><p>Chaque rapport combine le dimensionnement par catégorie et les prévisions avec une analyse du comportement des consommateurs, des prix et coûts des intrants, de la réglementation et de l\'étiquetage, ainsi que de la dynamique concurrentielle entre fabricants et marques.</p>',
                    ],
                ],
            ],
            [
                'slug' => 'healthcare',
                'translations' => [
                    'en' => [
                        'name' => 'Healthcare',
                        'meta_title' => 'Healthcare Market Research Reports & Industry Analysis',
                        'meta_description' => 'Healthcare market research spanning pharmaceuticals, biotechnology, medical devices, diagnostics, digital health and healthcare services, with market forecasts and competitive analysis by region.',
                        'description' => '<h2>Healthcare Market Research and Industry Insights</h2><p>Healthcare is being transformed by aging populations, biologics and cell and gene therapies, digital health, and a shift toward value-based and decentralized care. Our research covers pharmaceuticals and biotechnology, medical devices and equipment, in vitro and molecular diagnostics, healthcare IT, and provider and payer services.</p><p>Reports pair market sizing and forecasts with analysis of clinical pipelines, reimbursement and regulation, adoption of new technologies, and the competitive positioning of pharma, device and services companies. The insights support manufacturers, investors and providers in prioritizing therapeutic areas, geographies and product strategy.</p>',
                    ],
                    'ja' => [
                        'name' => 'ヘルスケア',
                        'meta_title' => 'ヘルスケアの市場調査レポートと業界分析',
                        'meta_description' => '医薬品・バイオテクノロジー、医療機器、体外診断・分子診断、ヘルスケアIT、医療提供者・支払者サービスを対象とするヘルスケア分野の市場調査レポートです。市場予測、臨床パイプライン、償還と規制、新技術の採用、製薬・機器・サービス企業の競合分析を主要地域別に提供し、治療領域・地域・製品戦略の優先順位付けを支援します。',
                        'description' => '<h2>ヘルスケアの市場調査と業界インサイト</h2><p>ヘルスケアは、高齢化、バイオ医薬品や細胞・遺伝子治療、デジタルヘルス、価値ベースおよび分散型ケアへの移行によって変革しています。当社の調査は、医薬品・バイオテクノロジー、医療機器・装置、体外診断・分子診断、ヘルスケアIT、医療提供者・支払者サービスを網羅します。</p><p>各レポートは、市場規模と予測に加え、臨床パイプライン、償還と規制、新技術の採用、製薬・機器・サービス企業の競争ポジションを分析します。これにより、メーカー、投資家、医療提供者は治療領域、地域、製品戦略の優先順位を付けられます。</p>',
                    ],
                    'ko' => [
                        'name' => '헬스케어',
                        'meta_title' => '헬스케어 시장 조사 보고서 및 산업 분석',
                        'meta_description' => '제약·바이오테크놀로지, 의료기기, 체외·분자 진단, 헬스케어 IT, 의료 제공자·지불자 서비스를 아우르는 헬스케어 부문 시장 조사 보고서입니다. 시장 전망, 임상 파이프라인, 급여와 규제, 신기술 채택, 주요 기업의 경쟁 분석을 주요 지역별로 제공하여 치료 영역·지역·제품 전략의 우선순위 설정을 지원합니다.',
                        'description' => '<h2>헬스케어 시장 조사 및 산업 인사이트</h2><p>헬스케어는 고령화, 바이오의약품과 세포·유전자 치료, 디지털 헬스, 가치 기반 및 분산형 의료로의 전환으로 변화하고 있습니다. 당사의 조사는 제약과 바이오테크놀로지, 의료기기와 장비, 체외 및 분자 진단, 헬스케어 IT, 의료 제공자·지불자 서비스를 다룹니다.</p><p>각 보고서는 시장 규모와 전망에 더해 임상 파이프라인, 급여와 규제, 신기술 채택, 제약·기기·서비스 기업의 경쟁 포지션을 분석합니다. 이를 통해 제조사, 투자자, 의료 제공자는 치료 영역, 지역, 제품 전략의 우선순위를 정할 수 있습니다.</p>',
                    ],
                    'zh' => [
                        'name' => '医疗健康',
                        'meta_title' => '医疗健康市场调研报告与行业分析',
                        'meta_description' => '本报告聚焦医疗健康领域的市场调研，涵盖制药与生物技术、医疗器械、体外与分子诊断、医疗信息化及医疗提供者与支付方服务。报告按主要地区提供市场预测、临床管线、医保报销与监管动态及主要企业竞争分析，助力制造商、投资者和医疗提供者确定治疗领域与产品战略优先级，并系统梳理全球主要区域市场竞争格局，助力企业把握增长机遇。',
                        'description' => '<h2>医疗健康市场调研与行业洞察</h2><p>人口老龄化、生物制剂与细胞及基因治疗、数字医疗以及向价值医疗和分散化医疗模式的转变正在深刻改变医疗健康行业。我们的研究涵盖制药与生物技术、医疗器械与设备、体外与分子诊断、医疗信息技术，以及医疗提供者与支付方服务。</p><p>每份报告在市场规模与预测基础上，分析临床管线、医保报销与监管、新技术应用，以及制药、器械及服务企业的竞争地位。这有助于制造商、投资者和医疗提供者确定治疗领域、地区及产品战略的优先级。</p>',
                    ],
                    'es' => [
                        'name' => 'Salud',
                        'meta_title' => 'Informes de Investigación de Mercado de Salud',
                        'meta_description' => 'Investigación de mercado de Salud: farmacéutica, biotecnología, dispositivos médicos, diagnóstico y salud digital, con previsiones de mercado y análisis competitivo por región del sector sanitario.',
                        'description' => '<h2>Investigación de Mercado y Perspectivas del Sector Salud</h2><p>El sector salud está siendo transformado por el envejecimiento de la población, los productos biológicos y las terapias celulares y génicas, la salud digital, y un giro hacia la atención basada en el valor y descentralizada. Nuestra investigación cubre productos farmacéuticos y biotecnología, dispositivos y equipos médicos, diagnóstico in vitro y molecular, TI sanitaria, y servicios de proveedores y pagadores.</p><p>Los informes combinan el dimensionamiento y las previsiones del mercado con el análisis de las carteras clínicas, el reembolso y la regulación, la adopción de nuevas tecnologías y el posicionamiento competitivo de las empresas farmacéuticas, de dispositivos y de servicios. Estos datos apoyan a fabricantes e inversores.</p>',
                    ],
                    'de' => [
                        'name' => 'Gesundheitswesen',
                        'meta_title' => 'Marktforschungsberichte Gesundheitswesen',
                        'meta_description' => 'Marktforschung zum Gesundheitswesen: Pharma, Biotechnologie, Medizinprodukte, Diagnostik, digitale Gesundheit und Gesundheitsdienstleistungen, mit Marktprognosen und regionaler Wettbewerbsanalyse.',
                        'description' => '<h2>Marktforschung und Brancheneinblicke Gesundheitswesen</h2><p>Das Gesundheitswesen wird durch die alternde Bevölkerung, Biologika sowie Zell- und Gentherapien, digitale Gesundheit und den Wandel hin zu wertbasierter und dezentralisierter Versorgung grundlegend verändert. Unsere Forschung umfasst Pharma und Biotechnologie, Medizinprodukte und -geräte, In-vitro- und Molekulardiagnostik, Gesundheits-IT sowie Leistungserbringer- und Kostenträgerdienstleistungen.</p><p>Die Berichte verbinden Marktgrößenschätzungen und Prognosen mit einer Analyse klinischer Pipelines, Erstattung und Regulierung, der Einführung neuer Technologien sowie der Wettbewerbsposition von Pharma-, Geräte- und Dienstleistungsunternehmen.</p>',
                    ],
                    'fr' => [
                        'name' => 'Santé',
                        'meta_title' => 'Rapports d\'Études de Marché Santé',
                        'meta_description' => 'Étude de marché de la Santé : pharmaceutique, biotechnologie, dispositifs médicaux, diagnostic et santé numérique, avec prévisions de marché et analyse concurrentielle par région du secteur sanitaire.',
                        'description' => '<h2>Étude de Marché et Perspectives du Secteur de la Santé</h2><p>Le secteur de la santé est transformé par le vieillissement de la population, les produits biologiques et les thérapies cellulaires et géniques, la santé numérique, et une évolution vers des soins fondés sur la valeur et décentralisés. Notre étude couvre les produits pharmaceutiques et la biotechnologie, les dispositifs et équipements médicaux, le diagnostic in vitro et moléculaire, les technologies de santé, ainsi que les services des prestataires et payeurs.</p><p>Les rapports combinent le dimensionnement du marché et les prévisions avec une analyse des pipelines cliniques, du remboursement et de la réglementation, de l\'adoption des nouvelles technologies et du positionnement concurrentiel des entreprises pharmaceutiques, de dispositifs et de services.</p>',
                    ],
                ],
            ],
            [
                'slug' => 'agriculture',
                'translations' => [
                    'en' => [
                        'name' => 'Agriculture',
                        'meta_title' => 'Agriculture Market Research Reports & Industry Analysis',
                        'meta_description' => 'Agriculture market research covering seeds and traits, crop protection, fertilizers, farm machinery, precision farming and animal nutrition, with demand forecasts and competitive analysis by region.',
                        'description' => '<h2>Agriculture Market Research and Industry Insights</h2><p>Agriculture is under pressure to raise yields and resilience while reducing inputs and emissions, driving adoption of precision farming, biologicals, improved genetics and digital tools. Our coverage spans seeds and traits, crop protection and fertilizers, farm machinery and equipment, irrigation, animal health and nutrition, and agricultural technology.</p><p>Each report combines market sizing and forecasts with analysis of commodity cycles, weather and policy risk, farm economics, and the strategies of input suppliers, equipment makers and agtech providers. These findings help agribusinesses, investors and technology vendors identify growth markets and align product and go-to-market plans.</p>',
                    ],
                    'ja' => [
                        'name' => '農業',
                        'meta_title' => '農業の市場調査レポートと業界分析',
                        'meta_description' => '種子・形質、作物保護剤・肥料、農業機械・設備、灌漑、動物用医薬品・栄養、精密農業や農業テクノロジーを対象とする農業分野の市場調査レポートです。需要予測、コモディティサイクル、天候と政策のリスク、農業経営の採算、投入資材・機械・アグテック企業の競合分析を地域別に提供し、成長市場の特定と市場投入計画の策定を支援します。',
                        'description' => '<h2>農業の市場調査と業界インサイト</h2><p>農業は、投入資材と排出を削減しつつ収量と強靭性を高めることが求められており、精密農業、バイオ製剤、改良された遺伝資源、デジタルツールの採用が進んでいます。当社の調査対象は、種子・形質、作物保護剤・肥料、農業機械・設備、灌漑、動物用医薬品・栄養、農業テクノロジーに及びます。</p><p>各レポートは、市場規模と予測に加え、コモディティサイクル、天候と政策のリスク、農業経営の採算、投入資材サプライヤー・機械メーカー・アグテック企業の戦略を分析します。これにより、アグリビジネス、投資家、技術ベンダーは成長市場を特定し、製品と市場投入計画を整合させられます。</p>',
                    ],
                    'ko' => [
                        'name' => '농업',
                        'meta_title' => '농업 시장 조사 보고서 및 산업 분석',
                        'meta_description' => '종자·형질, 작물 보호제·비료, 농기계·장비, 관개, 동물 건강·영양, 정밀 농업 및 농업 기술을 아우르는 농업 부문 시장 조사 보고서입니다. 수요 전망, 원자재 주기, 기상·정책 리스크, 영농 경제성, 주요 기업의 경쟁 분석을 지역별로 제공하여 성장 시장 발굴과 시장 진출 계획 수립을 지원합니다.',
                        'description' => '<h2>농업 시장 조사 및 산업 인사이트</h2><p>농업은 투입재와 배출을 줄이면서 수확량과 회복력을 높여야 하는 압박을 받고 있으며, 이에 따라 정밀 농업, 바이오 제제, 개량 유전자원, 디지털 도구의 채택이 확대되고 있습니다. 당사의 조사 범위는 종자·형질, 작물 보호제·비료, 농기계·장비, 관개, 동물 건강·영양, 농업 기술에 이릅니다.</p><p>각 보고서는 시장 규모와 전망에 더해 원자재 주기, 기상 및 정책 리스크, 영농 경제성, 투입재 공급업체·장비 제조사·애그테크 기업의 전략을 분석합니다. 이러한 결과는 농산업 기업, 투자자, 기술 공급업체가 성장 시장을 파악하고 제품 및 시장 진출 계획을 정렬하도록 돕습니다.</p>',
                    ],
                    'zh' => [
                        'name' => '农业',
                        'meta_title' => '农业市场调研报告与行业分析',
                        'meta_description' => '本报告聚焦农业领域的市场调研，涵盖种子与性状、作物保护及肥料、农业机械与设备、灌溉、动物保健营养、精准农业及农业科技。报告按地区提供需求预测、大宗商品周期及天气政策风险分析，以及主要企业竞争分析，助力农业企业、投资者和技术供应商锁定增长市场并规划战略，并系统梳理全球主要区域市场竞争格局，助力企业把握增长机遇。',
                        'description' => '<h2>农业市场调研与行业洞察</h2><p>农业正面临在降低投入品与排放的同时提高产量与韧性的压力，这推动了精准农业、生物制剂、优良遗传资源及数字化工具的广泛应用。我们的研究涵盖种子与性状、作物保护与肥料、农业机械与设备、灌溉、动物保健与营养以及农业科技。</p><p>每份报告在市场规模与预测基础上，分析大宗商品周期、天气与政策风险、农场经济效益，以及投入品供应商、机械制造商与农业科技企业的战略。这有助于农业企业、投资者和技术供应商锁定增长市场。</p>',
                    ],
                    'es' => [
                        'name' => 'Agricultura',
                        'meta_title' => 'Informes de Investigación de Mercado de Agricultura',
                        'meta_description' => 'Investigación de mercado de Agricultura: semillas, protección de cultivos, maquinaria agrícola, agricultura de precisión y nutrición animal, con previsiones y análisis competitivo por región.',
                        'description' => '<h2>Investigación de Mercado y Perspectivas del Sector Agrícola</h2><p>La agricultura está sometida a la presión de aumentar el rendimiento y la resiliencia mientras reduce insumos y emisiones, lo que impulsa la adopción de la agricultura de precisión, los biológicos, la genética mejorada y las herramientas digitales. Nuestra cobertura abarca semillas y rasgos, protección de cultivos y fertilizantes, maquinaria y equipos agrícolas, riego, salud y nutrición animal, y tecnología agrícola.</p><p>Cada informe combina el dimensionamiento del mercado y las previsiones con el análisis de los ciclos de las materias primas, el riesgo climático y político, la economía agrícola y las estrategias de proveedores de insumos, fabricantes de equipos y proveedores de agtech. Estos datos ayudan a las agroindustrias e inversores.</p>',
                    ],
                    'de' => [
                        'name' => 'Landwirtschaft',
                        'meta_title' => 'Marktforschungsberichte Landwirtschaft',
                        'meta_description' => 'Marktforschung zur Landwirtschaft: Saatgut und Merkmale, Pflanzenschutz, Landmaschinen, Präzisionslandwirtschaft und Tierernährung, mit Nachfrageprognosen und regionaler Wettbewerbsanalyse.',
                        'description' => '<h2>Marktforschung und Brancheneinblicke Landwirtschaft</h2><p>Die Landwirtschaft steht unter dem Druck, Erträge und Resilienz zu steigern und gleichzeitig Betriebsmittel und Emissionen zu reduzieren, was die Einführung von Präzisionslandwirtschaft, Biologika, verbesserter Genetik und digitalen Werkzeugen vorantreibt. Unsere Abdeckung umfasst Saatgut und Merkmale, Pflanzenschutz und Düngemittel, Landmaschinen und -geräte, Bewässerung, Tiergesundheit und -ernährung sowie Agrartechnologie.</p><p>Jeder Bericht verbindet Marktgrößenschätzungen und Prognosen mit einer Analyse von Rohstoffzyklen, Wetter- und Politikrisiken, landwirtschaftlicher Wirtschaftlichkeit sowie den Strategien von Betriebsmittellieferanten, Maschinenherstellern und Agtech-Anbietern.</p>',
                    ],
                    'fr' => [
                        'name' => 'Agriculture',
                        'meta_title' => 'Rapports d\'Études de Marché Agriculture',
                        'meta_description' => 'Étude de marché de l\'Agriculture : semences, protection des cultures, machines agricoles, agriculture de précision et nutrition animale, avec prévisions et analyse concurrentielle par région.',
                        'description' => '<h2>Étude de Marché et Perspectives du Secteur Agricole</h2><p>L\'agriculture subit une pression pour augmenter les rendements et la résilience tout en réduisant les intrants et les émissions, ce qui favorise l\'adoption de l\'agriculture de précision, des produits biologiques, de la génétique améliorée et des outils numériques. Notre couverture comprend les semences et caractères, la protection des cultures et les engrais, les machines et équipements agricoles, l\'irrigation, la santé et la nutrition animale, et les technologies agricoles.</p><p>Chaque rapport combine le dimensionnement du marché et les prévisions avec une analyse des cycles des matières premières, des risques climatiques et politiques, de l\'économie agricole et des stratégies des fournisseurs d\'intrants, fabricants d\'équipements et prestataires agtech.</p>',
                    ],
                ],
            ],
            [
                'slug' => 'packaging',
                'translations' => [
                    'en' => [
                        'name' => 'Packaging',
                        'meta_title' => 'Packaging Market Research Reports & Industry Analysis',
                        'meta_description' => 'Packaging market research across rigid and flexible plastics, paper and board, glass, metal cans and closures, plus sustainable and smart packaging, with demand forecasts and competitive analysis.',
                        'description' => '<h2>Packaging Market Research and Industry Insights</h2><p>Packaging demand is driven by e-commerce growth, food and beverage consumption and healthcare needs, while sustainability regulation and recycled-content targets are rapidly changing material choices. Our research covers rigid and flexible plastics, paper and board, glass, metal cans, caps and closures, labels, and smart and active packaging.</p><p>Reports pair material and format market sizing with analysis of resin and fiber pricing, regulatory pressure on single-use plastics, brand-owner commitments, and the competitive landscape of converters and material suppliers. The insights help packaging producers, brand owners and investors plan capacity, substitution and design strategy.</p>',
                    ],
                    'ja' => [
                        'name' => 'パッケージング',
                        'meta_title' => 'パッケージングの市場調査レポートと業界分析',
                        'meta_description' => '硬質・軟質プラスチック、紙・板紙、ガラス、金属缶、キャップ・クロージャー、ラベル、スマート/アクティブパッケージングを対象とするパッケージング分野の市場調査レポートです。素材・形態別の需要予測、樹脂と繊維の価格、規制圧力、ブランドオーナーの動向、主要企業の競合分析を提供し、生産能力と設計戦略の策定を支援します。',
                        'description' => '<h2>パッケージングの市場調査と業界インサイト</h2><p>パッケージング需要は、Eコマースの成長、食品・飲料の消費、ヘルスケアのニーズによって牽引される一方、サステナビリティ規制と再生材含有目標が素材選択を急速に変えています。当社の調査は、硬質・軟質プラスチック、紙・板紙、ガラス、金属缶、キャップ・クロージャー、ラベル、スマートおよびアクティブパッケージングを網羅します。</p><p>各レポートは、素材および形態別の市場規模に加え、樹脂と繊維の価格、使い捨てプラスチックへの規制圧力、ブランドオーナーのコミットメント、コンバーターと素材サプライヤーの競争環境を分析します。これにより、パッケージングメーカー、ブランドオーナー、投資家は生産能力、代替、設計戦略を計画できます。</p>',
                    ],
                    'ko' => [
                        'name' => '패키징',
                        'meta_title' => '패키징 시장 조사 보고서 및 산업 분석',
                        'meta_description' => '경질·연질 플라스틱, 종이·판지, 유리, 금속 캔, 캡·마개, 라벨, 스마트/액티브 패키징을 아우르는 패키징 부문 시장 조사 보고서입니다. 소재·형태별 수요 전망, 수지와 섬유 가격, 규제 압력, 브랜드 오너 동향, 주요 기업의 경쟁 분석을 제공하여 생산능력과 설계 전략 수립을 지원합니다.',
                        'description' => '<h2>패키징 시장 조사 및 산업 인사이트</h2><p>패키징 수요는 이커머스 성장, 식음료 소비, 헬스케어 수요에 힘입어 확대되는 한편, 지속가능성 규제와 재활용 원료 함량 목표가 소재 선택을 빠르게 바꾸고 있습니다. 당사의 조사는 경질 및 연질 플라스틱, 종이·판지, 유리, 금속 캔, 캡·마개, 라벨, 스마트 및 액티브 패키징을 다룹니다.</p><p>각 보고서는 소재 및 형태별 시장 규모와 함께 수지와 섬유 가격, 일회용 플라스틱에 대한 규제 압력, 브랜드 오너의 약속, 컨버터와 소재 공급업체의 경쟁 구도를 분석합니다. 이를 통해 패키징 생산업체, 브랜드 오너, 투자자는 생산능력, 대체, 설계 전략을 계획할 수 있습니다.</p>',
                    ],
                    'zh' => [
                        'name' => '包装',
                        'meta_title' => '包装市场调研报告与行业分析',
                        'meta_description' => '本报告聚焦包装领域的市场调研，涵盖硬质与软质塑料、纸与纸板、玻璃、金属罐及盖子、标签、智能与主动包装。报告按材料与形态提供需求预测、树脂纤维价格及监管趋势分析，以及转换商与材料供应商的竞争分析，助力包装生产商、品牌方和投资者规划产能与设计战略，并系统梳理全球主要区域市场竞争格局，助力企业把握增长机遇。',
                        'description' => '<h2>包装市场调研与行业洞察</h2><p>电子商务增长、食品饮料消费及医疗健康需求推动包装需求增长，而可持续发展法规和再生材料含量目标正在迅速改变材料选择。我们的研究涵盖硬质与软质塑料、纸与纸板、玻璃、金属罐、盖子及封口、标签，以及智能与主动包装。</p><p>每份报告在材料与形态市场规模基础上，分析树脂与纤维价格、一次性塑料监管压力、品牌方承诺，以及转换商与材料供应商的竞争格局。这有助于包装生产商、品牌方和投资者规划产能与设计战略。</p>',
                    ],
                    'es' => [
                        'name' => 'Envases y Embalajes',
                        'meta_title' => 'Informes de Investigación de Mercado de Envases y Embalajes',
                        'meta_description' => 'Investigación de mercado de Envases y Embalajes: plásticos rígidos y flexibles, papel y cartón, vidrio, latas metálicas y envases inteligentes, con previsiones de demanda y análisis competitivo.',
                        'description' => '<h2>Investigación de Mercado y Perspectivas del Sector de Envases y Embalajes</h2><p>La demanda de envases y embalajes está impulsada por el crecimiento del comercio electrónico, el consumo de alimentos y bebidas y las necesidades sanitarias, mientras que la regulación de sostenibilidad y los objetivos de contenido reciclado cambian rápidamente la elección de materiales. Nuestra investigación cubre plásticos rígidos y flexibles, papel y cartón, vidrio, latas metálicas, tapas y cierres, etiquetas, y envases inteligentes y activos.</p><p>Los informes combinan el dimensionamiento por material y formato con el análisis de precios de resinas y fibras, la presión regulatoria sobre plásticos de un solo uso, los compromisos de las marcas y el panorama competitivo de convertidores y proveedores de materiales.</p>',
                    ],
                    'de' => [
                        'name' => 'Verpackung',
                        'meta_title' => 'Marktforschungsberichte Verpackung',
                        'meta_description' => 'Marktforschung zur Verpackung: starre und flexible Kunststoffe, Papier und Karton, Glas, Metalldosen und Verschlüsse sowie intelligente Verpackungen, mit Nachfrageprognosen und Wettbewerbsanalyse.',
                        'description' => '<h2>Marktforschung und Brancheneinblicke Verpackung</h2><p>Die Nachfrage nach Verpackungen wird durch das Wachstum des E-Commerce, den Konsum von Lebensmitteln und Getränken sowie den Bedarf im Gesundheitswesen angetrieben, während Nachhaltigkeitsvorschriften und Rezyklatanteilsziele die Materialwahl rasch verändern. Unsere Forschung umfasst starre und flexible Kunststoffe, Papier und Karton, Glas, Metalldosen, Verschlüsse, Etiketten sowie intelligente und aktive Verpackungen.</p><p>Die Berichte verbinden die Marktgrößenschätzung nach Material und Format mit einer Analyse von Harz- und Faserpreisen, regulatorischem Druck auf Einwegkunststoffe, Markenverpflichtungen sowie der Wettbewerbslandschaft von Verarbeitern und Materiallieferanten.</p>',
                    ],
                    'fr' => [
                        'name' => 'Emballage',
                        'meta_title' => 'Rapports d\'Études de Marché Emballage',
                        'meta_description' => 'Étude de marché de l\'Emballage : plastiques rigides et souples, papier et carton, verre, boîtes métalliques et emballage intelligent, avec prévisions et analyse concurrentielle par région.',
                        'description' => '<h2>Étude de Marché et Perspectives du Secteur de l\'Emballage</h2><p>La demande d\'emballage est portée par la croissance du commerce électronique, la consommation alimentaire et de boissons et les besoins de santé, tandis que la réglementation en matière de durabilité et les objectifs de contenu recyclé transforment rapidement le choix des matériaux. Notre étude couvre les plastiques rigides et souples, le papier et le carton, le verre, les boîtes métalliques, les bouchons et fermetures, les étiquettes, ainsi que l\'emballage intelligent et actif.</p><p>Les rapports combinent le dimensionnement par matériau et format avec une analyse des prix des résines et fibres, de la pression réglementaire sur les plastiques à usage unique, des engagements des marques et du paysage concurrentiel des transformateurs et fournisseurs.</p>',
                    ],
                ],
            ],
        ];
    }
}

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

        $languages = DB::table('languages')->pluck('id', 'code'); // ['en' => 1, 'ja' => 2, 'ko' => 3]

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
                ],
            ],
        ];
    }
}

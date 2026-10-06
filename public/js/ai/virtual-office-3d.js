/**
 * COOCA AI — The Sims Interactive 3D Virtual Office Engine
 * Built with Three.js (r128) & WebGL
 *
 * Architecture:
 * - 1-Floor Modern Corporate Headquarters Campus (Isometric Cutaway)
 *     - Central Grand Atrium & Waiting Rotunda (Y = 0)
 *         * Front Entrance Gates & Landscaping
 *         * Entrance Monument: "COOCA AI Virtual Office"
 *         * Reception Counter with illuminated sign & receptionist
 *         * Central Circular Rotunda Lounge (Circular banquette around lush planter & tree)
 *         * Northward connecting glass corridor
 *     - North Center: Executive Office Wing
 *         * Header Banner: "👑 Executive Office" (Strategy • Finance • Decisions)
 *         * AI CEO Workstation (Leading)
 *         * AI CFO Workstation (Working) with financial monitor
 *         * Business, Finance & Reporting workstations
 *         * Large Executive Boardroom Table (Circular/oval with 8 chairs & COOCA BOARDROOM)
 *     - West Wing: Growth Office (Purple Theme)
 *         * Header Banner: "📈 Growth Office" (Marketing • Sales • Content • Customers)
 *         * Creative Meeting Room
 *         * Department Studios: CMO, Sales Director, Marketing, Content, Social Media, Sales, Customer
 *         * South-West: "Meeting Room" & "Focus Room" (Quiet pod)
 *     - East Wing: Operations Office (Teal/Green Theme)
 *         * Header Banner: "⚙️ Operations Office" (Inventory • Purchasing • Marketplace)
 *         * Operations Meeting Table
 *         * Workstations: COO, Inventory Agent, Purchasing Agent, Marketplace Agent
 *         * South-East: Pantry & Coffee Breakroom (Dining tables & espresso bar)
 *         * South-East: "Control Center" (Monitoring • Analytics with multi-screen video wall)
 *     - Perimeter: Parking stalls with asphalt, painted markings, and parked 3D cars
 * - The Sims Autonomous Workforce (All on 1 single level, Y = 0):
 *     - Full articulated humanoid rigs, rotating emerald Plumbob diamonds, and speech bubbles
 *     - Smooth floor-constrained navigation between desks, rotunda lounge, boardroom, pantry, and control center
 */

(function (window) {
    'use strict';

    class Cooca3DOffice {
        constructor(container, options = {}) {
            this.container = typeof container === 'string' ? document.getElementById(container) : container;
            if (!this.container) {
                console.error('[Cooca3DOffice] Container element not found.');
                return;
            }

            this.options = Object.assign({
                mode: 'unified', // 'unified', 'executive', 'operations', 'growth', 'lobby'
                agents: {},
                tasks: [],
                proposals: [],
                presets: {},
                businessData: {},
                liveMetricsUrl: '/cooca-ai/live-metrics',
                onSelectAgent: null,
                isNight: false,
                audioEnabled: false,
            }, options);

            this.agents = this.options.agents || {};
            this.mode = this.options.mode || 'unified';
            this.businessData = this.options.businessData || {};
            this.liveMetricsUrl = this.options.liveMetricsUrl || '/cooca-ai/live-metrics';
            this.pollInterval = null;
            this.initLiveMetricsPolling();
            this.isNight = typeof this.options.isNight === 'boolean'
                ? this.options.isNight
                : (typeof document !== 'undefined' && document.documentElement.classList.contains('dark'));
            this.audioEnabled = !!this.options.audioEnabled;
            this.onSelectAgent = this.options.onSelectAgent || null;

            this.scene = null;
            this.camera = null;
            this.renderer = null;
            this.controls = null;
            this.clock = new THREE.Clock();

            // Registered 3D meshes & collections
            this.agentMeshes = new Map();
            this.simsCharacters = new Map();
            this.interactiveObjects = [];
            this.blinkingLeds = [];
            this.roomSignMeshes = [];
            this.hoveredObject = null;
            this.selectedAgentRole = null;

            // Camera lerping state
            this.targetCameraPos = null;
            this.targetLookAt = null;
            this.isLerpingCamera = false;

            // Audio Context for synthesized sound FX
            this.audioCtx = null;
            this.lastSoundTime = 0;

            // Active Dynamic Monitor CanvasTextures (Live working screens)
            this.screenCanvases = new Map();
            this.activeScreenTextures = [];
            this.lastScreenUpdateTime = 0;

            // =========================================================================
            // 1-FLOOR NAVIGATIONAL GRAPH (100% ALIGNED WITH 2D ARCHITECTURAL BLUEPRINT)
            // Strictly interior bounds: X: [-24.5, 24.5], Z: [-15.5, 10.5], Y = 0
            // =========================================================================
            this.navNodes = {
                // --- 1. CENTRAL SPINE (Atrium, Reception & Grand Entrance) ---
                'atrium_center':        new THREE.Vector3(0, 0, 0.5),
                'atrium_rotunda_north': new THREE.Vector3(0, 0, -2.2),
                'atrium_rotunda_south': new THREE.Vector3(0, 0, 3.2),
                'atrium_rotunda_west':  new THREE.Vector3(-4.0, 0, 0.5),
                'atrium_rotunda_east':  new THREE.Vector3(4.0, 0, 0.5),
                'reception_front':      new THREE.Vector3(0, 0, 6.0),
                'entrance_mat':         new THREE.Vector3(0, 0, 9.2),
                'entrance_doors':       new THREE.Vector3(0, 0, 10.5),

                // --- 2. EXECUTIVE SUITE (Center-North, Doorway at x=0, z=-4.2) ---
                'exec_door':            new THREE.Vector3(0, 0, -4.2),
                'exec_hall_center':     new THREE.Vector3(0, 0, -6.5),
                'exec_boardroom_table': new THREE.Vector3(0, 0, -7.5),
                'exec_ceo_approach':    new THREE.Vector3(0, 0, -11.0),
                'exec_ceo_desk':        new THREE.Vector3(0, 0, -12.8),
                'exec_hall_west':       new THREE.Vector3(-4.5, 0, -8.5),
                'exec_cfo_desk':        new THREE.Vector3(-4.5, 0, -11.5),
                'exec_hall_east':       new THREE.Vector3(4.5, 0, -8.5),
                'exec_business_desk':   new THREE.Vector3(4.5, 0, -11.5),

                // --- 3. WEST WING: MARKETING, SALES & MEETING ROOM ---
                // Main West Wing Doorway from Atrium (x=-7.2, z=-0.5)
                'growth_door':          new THREE.Vector3(-7.2, 0, -0.5),
                'growth_spine':         new THREE.Vector3(-10.5, 0, -0.5),
                
                // Marketing Room (Top-Left, Doorway at x=-10.5, z=-5.0)
                'mkt_door':             new THREE.Vector3(-10.5, 0, -5.0),
                'mkt_collab_table':     new THREE.Vector3(-16.0, 0, -8.5),
                'mkt_aisle':            new THREE.Vector3(-16.0, 0, -11.2),
                'mkt_desk_marketing':   new THREE.Vector3(-20.5, 0, -13.0),
                'mkt_desk_content':     new THREE.Vector3(-16.0, 0, -13.0),
                'mkt_desk_social':      new THREE.Vector3(-11.5, 0, -13.0),

                // Sales Room (Mid-Left, z = -5.0 to 2.5)
                'sales_hall':           new THREE.Vector3(-16.0, 0, -1.8),
                'sales_workbench':      new THREE.Vector3(-16.0, 0, 0.0),
                'sales_desk_sales':     new THREE.Vector3(-18.5, 0, -3.5),
                'sales_desk_customer':  new THREE.Vector3(-13.5, 0, -3.5),

                // Meeting Room (Bottom-Left, Doorway at x=-10.5, z=2.5)
                'meeting_door':         new THREE.Vector3(-10.5, 0, 2.5),
                'meeting_table':        new THREE.Vector3(-16.0, 0, 6.5),
                'meeting_tv':           new THREE.Vector3(-16.0, 0, 3.8),
                'meeting_lounge_bench': new THREE.Vector3(-21.5, 0, 6.5),

                // --- 4. EAST WING: OPERATIONS, SERVER, FINANCE, DOCS, HR, PANTRY, RESTROOM ---
                // Main East Wing Doorway from Atrium (x=7.2, z=-0.5)
                'ops_door':             new THREE.Vector3(7.2, 0, -0.5),
                'ops_spine':            new THREE.Vector3(10.5, 0, -0.5),

                // Operations Room (Top-Right, Doorway at x=10.5, z=-5.0)
                'ops_room_door':        new THREE.Vector3(10.5, 0, -5.0),
                'ops_collab_table':     new THREE.Vector3(13.5, 0, -8.5),
                'ops_aisle':            new THREE.Vector3(13.5, 0, -11.2),
                'ops_desk_inventory':   new THREE.Vector3(10.0, 0, -13.0),
                'ops_desk_purchasing':  new THREE.Vector3(13.5, 0, -13.0),
                'ops_desk_marketplace': new THREE.Vector3(17.0, 0, -13.0),

                // Server Room & IT Support (Far Top-Right, Doorway at x=18.0, z=-10.5)
                'server_door':          new THREE.Vector3(18.0, 0, -10.5),
                'server_room_bay':      new THREE.Vector3(21.25, 0, -12.5),
                'it_support_desk':      new THREE.Vector3(21.25, 0, -7.5),

                // Finance Room (Mid-Right, Doorway at x=13.5, z=-0.5)
                'finance_hall':         new THREE.Vector3(13.0, 0, -1.5),
                'finance_desk_fin':     new THREE.Vector3(11.0, 0, -3.0),
                'finance_desk_rep':     new THREE.Vector3(15.0, 0, -3.0),

                // Document Room (Far Mid-Right, x=18.0 to 24.5, z=-5.0 to 1.5)
                'doc_door':             new THREE.Vector3(18.0, 0, -1.8),
                'document_archive':     new THREE.Vector3(21.25, 0, -1.75),

                // HR / People Desk (East Wing Operations near Server Room Doorway at x=18.0, z=-10.5)
                'hr_desk':              new THREE.Vector3(16.0, 0, -6.6),

                // Pantry & Bistro Lounge (Direct Dedicated Entrance from Central Atrium at x=7.2, z=4.0)
                'pantry_atrium_door':   new THREE.Vector3(7.2, 0, 4.0),
                'pantry_lounge':        new THREE.Vector3(11.35, 0, 4.15),
                'pantry_door':          new THREE.Vector3(15.5, 0, 4.2),
                'pantry_dining_area':   new THREE.Vector3(20.0, 0, 4.25),
                'pantry_kitchenette':   new THREE.Vector3(23.5, 0, 4.25),

                // Restroom (Far Bottom-Right, Doorway at x=19.5, z=6.8)
                'restroom_door':        new THREE.Vector3(19.5, 0, 6.8),
                'restroom_area':        new THREE.Vector3(19.8, 0, 7.8),
                'restroom_vanity':      new THREE.Vector3(22.2, 0, 8.2),
                'restroom_stalls':      new THREE.Vector3(15.2, 0, 7.6),
                'restroom_cubicle_1':   new THREE.Vector3(8.5, 0, 7.6),
                'restroom_cubicle_3':   new THREE.Vector3(11.5, 0, 7.6)
            };

            this.navAdjacency = {
                // Central Spine
                'atrium_center':        ['atrium_rotunda_north', 'atrium_rotunda_south', 'atrium_rotunda_west', 'atrium_rotunda_east'],
                'atrium_rotunda_north': ['atrium_center', 'exec_door'],
                'atrium_rotunda_south': ['atrium_center', 'reception_front', 'pantry_atrium_door'],
                'atrium_rotunda_west':  ['atrium_center', 'growth_door'],
                'atrium_rotunda_east':  ['atrium_center', 'ops_door'],
                'reception_front':      ['atrium_rotunda_south', 'entrance_mat'],
                'entrance_mat':         ['reception_front', 'entrance_doors'],
                'entrance_doors':       ['entrance_mat'],

                // Executive Wing
                'exec_door':            ['atrium_rotunda_north', 'exec_hall_center'],
                'exec_hall_center':     ['exec_door', 'exec_boardroom_table', 'exec_ceo_approach', 'exec_hall_west', 'exec_hall_east'],
                'exec_boardroom_table': ['exec_hall_center'],
                'exec_ceo_approach':    ['exec_hall_center', 'exec_ceo_desk'],
                'exec_ceo_desk':        ['exec_ceo_approach'],
                'exec_hall_west':       ['exec_hall_center', 'exec_cfo_desk'],
                'exec_cfo_desk':        ['exec_hall_west'],
                'exec_hall_east':       ['exec_hall_center', 'exec_business_desk'],
                'exec_business_desk':   ['exec_hall_east'],

                // West Wing (Growth, Marketing, Sales, Meeting)
                'growth_door':          ['atrium_rotunda_west', 'growth_spine'],
                'growth_spine':         ['growth_door', 'mkt_door', 'sales_hall', 'meeting_door'],
                
                // Marketing
                'mkt_door':             ['growth_spine', 'mkt_collab_table', 'mkt_aisle'],
                'mkt_collab_table':     ['mkt_door', 'mkt_aisle'],
                'mkt_aisle':            ['mkt_collab_table', 'mkt_desk_marketing', 'mkt_desk_content', 'mkt_desk_social'],
                'mkt_desk_marketing':   ['mkt_aisle'],
                'mkt_desk_content':     ['mkt_aisle'],
                'mkt_desk_social':      ['mkt_aisle'],

                // Sales
                'sales_hall':           ['growth_spine', 'sales_workbench', 'sales_desk_sales', 'sales_desk_customer'],
                'sales_workbench':      ['sales_hall'],
                'sales_desk_sales':     ['sales_hall'],
                'sales_desk_customer':  ['sales_hall'],

                // Meeting Room
                'meeting_door':         ['growth_spine', 'meeting_table', 'meeting_tv'],
                'meeting_table':        ['meeting_door', 'meeting_lounge_bench'],
                'meeting_tv':           ['meeting_door'],
                'meeting_lounge_bench': ['meeting_table'],

                // East Wing (Operations, Server, Finance, Docs, HR, Pantry, Restroom)
                'ops_door':             ['atrium_rotunda_east', 'ops_spine'],
                'ops_spine':            ['ops_door', 'ops_room_door', 'finance_hall'],

                // Operations & Server (HR now seated here near Server Room)
                'ops_room_door':        ['ops_spine', 'ops_collab_table', 'ops_aisle', 'server_door'],
                'ops_collab_table':     ['ops_room_door', 'ops_aisle'],
                'ops_aisle':            ['ops_collab_table', 'ops_desk_inventory', 'ops_desk_purchasing', 'ops_desk_marketplace', 'hr_desk'],
                'ops_desk_inventory':   ['ops_aisle'],
                'ops_desk_purchasing':  ['ops_aisle'],
                'ops_desk_marketplace': ['ops_aisle'],
                'server_door':          ['ops_room_door', 'server_room_bay', 'it_support_desk', 'hr_desk'],
                'server_room_bay':      ['server_door'],
                'it_support_desk':      ['server_door'],
                'hr_desk':              ['ops_aisle', 'server_door'],

                // Finance & Docs (Secured private wing - NEVER routes into Pantry)
                'finance_hall':         ['ops_spine', 'finance_desk_fin', 'finance_desk_rep', 'doc_door'],
                'finance_desk_fin':     ['finance_hall'],
                'finance_desk_rep':     ['finance_hall'],
                'doc_door':             ['finance_hall', 'document_archive'],
                'document_archive':     ['doc_door'],

                // Pantry & Bistro Lounge (Direct Access from Atrium)
                'pantry_atrium_door':   ['atrium_rotunda_south', 'pantry_lounge'],
                'pantry_lounge':        ['pantry_atrium_door', 'pantry_door'],
                'pantry_door':          ['pantry_lounge', 'pantry_dining_area', 'pantry_kitchenette', 'restroom_door'],
                'pantry_dining_area':   ['pantry_door', 'pantry_kitchenette'],
                'pantry_kitchenette':   ['pantry_dining_area'],
                'restroom_door':        ['pantry_door', 'restroom_area'],
                'restroom_area':        ['restroom_door', 'restroom_vanity', 'restroom_stalls'],
                'restroom_vanity':      ['restroom_area'],
                'restroom_stalls':      ['restroom_area', 'restroom_cubicle_3'],
                'restroom_cubicle_3':   ['restroom_stalls', 'restroom_cubicle_1'],
                'restroom_cubicle_1':   ['restroom_cubicle_3']
            };

            this.facilityWaypoints = {
                rotundaLounge1: this.navNodes.atrium_rotunda_north,
                rotundaLounge2: this.navNodes.atrium_rotunda_south,
                pantryBreakroom: this.navNodes.pantry_dining_area,
                boardroomTable: this.navNodes.exec_boardroom_table,
                growthMeetingRoom: this.navNodes.meeting_table,
                controlCenter: this.navNodes.it_support_desk,
            };

            // Centralized Chair Registry for Single-Occupancy Guaranteed Seating
            this.chairRegistry = new Map();
            this.liveMonitors = [];

            // Lights
            this.dirLight = null;
            this.ambientLight = null;
            this.pointLights = [];
            this.officeLampMaterials = [];
            this.deskLampMaterials = [];

            // POV & Direct Player Agent Control State
            this.isPOVMode = false;
            this.povRoleKey = null;
            this.povType = 'third_person'; // 'third_person' or 'first_person'
            this.activeMovementKeys = new Set();
            this.povPitch = 0;
            this.onPOVChange = this.options.onPOVChange || null;
            this.isMouseDown = false;
            this.prevMousePos = { x: 0, y: 0 };

            // Interactive Doors & Solid Wall Colliders
            this.doors = new Map();
            this.wallColliders = [];

            // Dynamic Urban Environment Collections
            this.cityVehicles = [];
            this.cityNPCs = [];
            this.skyHelicopter = null;
            this.mainRotorMesh = null;
            this.tailRotorMesh = null;
            this.heliStrobeLight = null;
            this.helicopterFlightTimer = 0;

            // Event Listeners
            this._onResize = this.onResize.bind(this);
            this._onPointerMove = this.onPointerMove.bind(this);
            this._onPointerClick = this.onPointerClick.bind(this);
            this._onKeyDown = this.handleKeyDown.bind(this);
            this._onKeyUp = this.handleKeyUp.bind(this);
            this._onMouseDown = (e) => { 
                this.isMouseDown = true; 
                this.prevMousePos = { x: e.clientX, y: e.clientY }; 
                this._clickStartPos = { x: e.clientX, y: e.clientY };
            };
            this._onMouseUp = () => { this.isMouseDown = false; };

            this.raycaster = new THREE.Raycaster();
            this.mouse = new THREE.Vector2();

            this.init();
        }



        // =========================================================================
        // COMPLETE CHAIR REGISTRY (100% CORRESPONDING TO 2D BLUEPRINT FURNITURE)
        // Over 70 individual, single-occupancy tracked seats across the campus
        // =========================================================================
        initChairRegistry() {
            this.chairRegistry = new Map();

            const register = (id, facility, x, z, rot, approachKey, seatHeight = 0.46) => {
                const approachNode = this.navNodes[approachKey] ? this.navNodes[approachKey].clone() : new THREE.Vector3(x, 0, z);
                this.chairRegistry.set(id, {
                    id,
                    facility,
                    pos: new THREE.Vector3(x, 0, z),
                    rot,
                    approachNode,
                    seatHeight,
                    occupiedBy: null
                });
            };

            // 1. Executive Boardroom: 8 discrete sofa & armchair seats (centered around x = 0, z = -7.5)
            // North sofa (3 seats facing South rot = 0)
            register('boardroom_n1', 'boardroom', -1.2, -8.8, 0, 'exec_boardroom_table', 0.46);
            register('boardroom_n2', 'boardroom',  0.0, -8.8, 0, 'exec_boardroom_table', 0.46);
            register('boardroom_n3', 'boardroom',  1.2, -8.8, 0, 'exec_boardroom_table', 0.46);
            // South sofa (3 seats facing North rot = Math.PI)
            register('boardroom_s1', 'boardroom', -1.2, -6.2, Math.PI, 'exec_boardroom_table', 0.46);
            register('boardroom_s2', 'boardroom',  0.0, -6.2, Math.PI, 'exec_boardroom_table', 0.46);
            register('boardroom_s3', 'boardroom',  1.2, -6.2, Math.PI, 'exec_boardroom_table', 0.46);
            // West & East armchairs
            register('boardroom_w1', 'boardroom', -2.5, -7.5, Math.PI / 2, 'exec_boardroom_table', 0.46);
            register('boardroom_e1', 'boardroom',  2.5, -7.5, -Math.PI / 2, 'exec_boardroom_table', 0.46);

            // 2. Meeting Room Conference Table: 8 seats (centered at x = -16.0, z = 6.5)
            // 3 North seats (facing South rot = 0)
            register('meeting_n1', 'meeting', -18.2, 5.4, 0, 'meeting_table', 0.46);
            register('meeting_n2', 'meeting', -16.0, 5.4, 0, 'meeting_table', 0.46);
            register('meeting_n3', 'meeting', -13.8, 5.4, 0, 'meeting_table', 0.46);
            // 3 South seats (facing North rot = Math.PI)
            register('meeting_s1', 'meeting', -18.2, 7.6, Math.PI, 'meeting_table', 0.46);
            register('meeting_s2', 'meeting', -16.0, 7.6, Math.PI, 'meeting_table', 0.46);
            register('meeting_s3', 'meeting', -13.8, 7.6, Math.PI, 'meeting_table', 0.46);
            // 1 West & 1 East seats
            register('meeting_w1', 'meeting', -19.6, 6.5, Math.PI / 2, 'meeting_table', 0.46);
            register('meeting_e1', 'meeting', -12.4, 6.5, -Math.PI / 2, 'meeting_table', 0.46);

            // 3. Marketing 8-Seater Collaboration Table (x = -16.0, z = -8.5)
            register('mkt_collab_n1', 'collab_mkt', -18.4, -9.4, 0, 'mkt_collab_table', 0.46);
            register('mkt_collab_n2', 'collab_mkt', -16.8, -9.4, 0, 'mkt_collab_table', 0.46);
            register('mkt_collab_n3', 'collab_mkt', -15.2, -9.4, 0, 'mkt_collab_table', 0.46);
            register('mkt_collab_n4', 'collab_mkt', -13.6, -9.4, 0, 'mkt_collab_table', 0.46);
            register('mkt_collab_s1', 'collab_mkt', -18.4, -7.6, Math.PI, 'mkt_collab_table', 0.46);
            register('mkt_collab_s2', 'collab_mkt', -16.8, -7.6, Math.PI, 'mkt_collab_table', 0.46);
            register('mkt_collab_s3', 'collab_mkt', -15.2, -7.6, Math.PI, 'mkt_collab_table', 0.46);
            register('mkt_collab_s4', 'collab_mkt', -13.6, -7.6, Math.PI, 'mkt_collab_table', 0.46);

            // 4. Sales 8-Seater Long Workbench (x = -16.0, z = 0.0)
            register('sales_wb_n1', 'collab_sales', -18.4, -0.9, 0, 'sales_workbench', 0.46);
            register('sales_wb_n2', 'collab_sales', -16.8, -0.9, 0, 'sales_workbench', 0.46);
            register('sales_wb_n3', 'collab_sales', -15.2, -0.9, 0, 'sales_workbench', 0.46);
            register('sales_wb_n4', 'collab_sales', -13.6, -0.9, 0, 'sales_workbench', 0.46);
            register('sales_wb_s1', 'collab_sales', -18.4,  0.9, Math.PI, 'sales_workbench', 0.46);
            register('sales_wb_s2', 'collab_sales', -16.8,  0.9, Math.PI, 'sales_workbench', 0.46);
            register('sales_wb_s3', 'collab_sales', -15.2,  0.9, Math.PI, 'sales_workbench', 0.46);
            register('sales_wb_s4', 'collab_sales', -13.6,  0.9, Math.PI, 'sales_workbench', 0.46);

            // 5. Operations 8-Seater Collaboration Workbench (x = 13.5, z = -8.5)
            register('ops_collab_n1', 'collab_ops', 11.1, -9.4, 0, 'ops_collab_table', 0.46);
            register('ops_collab_n2', 'collab_ops', 12.7, -9.4, 0, 'ops_collab_table', 0.46);
            register('ops_collab_n3', 'collab_ops', 14.3, -9.4, 0, 'ops_collab_table', 0.46);
            register('ops_collab_n4', 'collab_ops', 15.9, -9.4, 0, 'ops_collab_table', 0.46);
            register('ops_collab_s1', 'collab_ops', 11.1, -7.6, Math.PI, 'ops_collab_table', 0.46);
            register('ops_collab_s2', 'collab_ops', 12.7, -7.6, Math.PI, 'ops_collab_table', 0.46);
            register('ops_collab_s3', 'collab_ops', 14.3, -7.6, Math.PI, 'ops_collab_table', 0.46);
            register('ops_collab_s4', 'collab_ops', 15.9, -7.6, Math.PI, 'ops_collab_table', 0.46);

            // 6. Central Rotunda Lounge: 8 circular banquette seats around central tree (x = 0, z = 0.5, r = 2.15)
            for (let k = 0; k < 8; k++) {
                const angle = k * (Math.PI / 4);
                const rx = Math.cos(angle) * 2.15;
                const rz = 0.5 + Math.sin(angle) * 2.15;
                const rFacing = Math.atan2(rx, rz - 0.5);
                let approach = 'atrium_center';
                if (rz > 1.5) approach = 'atrium_rotunda_south';
                else if (rz < -0.5) approach = 'atrium_rotunda_north';
                else if (rx < 0) approach = 'atrium_rotunda_west';
                else approach = 'atrium_rotunda_east';
                register(`rotunda_${k}`, 'rotunda', rx, rz, rFacing, approach, 0.46);
            }

            // 7. Pantry & Lounge: 12 dining chairs across 3 round cafe tables (matching 2D Blueprint exactly)
            // Table 1 (Top Left: x = 18.5, z = 3.2)
            register('pantry_t1_n', 'pantry', 18.5, 2.2, 0, 'pantry_dining_area', 0.46);
            register('pantry_t1_s', 'pantry', 18.5, 4.2, Math.PI, 'pantry_dining_area', 0.46);
            register('pantry_t1_w', 'pantry', 17.5, 3.2, Math.PI / 2, 'pantry_dining_area', 0.46);
            register('pantry_t1_e', 'pantry', 19.5, 3.2, -Math.PI / 2, 'pantry_dining_area', 0.46);
            // Table 2 (Top Right: x = 22.0, z = 3.2)
            register('pantry_t2_n', 'pantry', 22.0, 2.2, 0, 'pantry_dining_area', 0.46);
            register('pantry_t2_s', 'pantry', 22.0, 4.2, Math.PI, 'pantry_dining_area', 0.46);
            register('pantry_t2_w', 'pantry', 21.0, 3.2, Math.PI / 2, 'pantry_dining_area', 0.46);
            register('pantry_t2_e', 'pantry', 23.0, 3.2, -Math.PI / 2, 'pantry_dining_area', 0.46);
            // Table 3 (Bottom Center: x = 20.25, z = 5.4)
            register('pantry_t3_n', 'pantry', 20.25, 4.4, 0, 'pantry_dining_area', 0.46);
            register('pantry_t3_s', 'pantry', 20.25, 6.4, Math.PI, 'pantry_dining_area', 0.46);
            register('pantry_t3_w', 'pantry', 19.25, 5.4, Math.PI / 2, 'pantry_dining_area', 0.46);
            register('pantry_t3_e', 'pantry', 21.25, 5.4, -Math.PI / 2, 'pantry_dining_area', 0.46);

            // 8. IT Support Operator Console: 1 operator chair (x = 21.25, z = -7.875, facing north Math.PI towards desk)
            register('control_1', 'control', 21.25, -7.875, Math.PI, 'it_support_desk', 0.46);
        }

        reserveSeat(facilityType, roleKey) {
            if (!this.chairRegistry) return null;

            // Release any current non-desk chair this agent is occupying
            this.releaseSeat(roleKey);

            // If requesting desk, deterministically return this agent's dedicated private workstation chair
            if (facilityType === 'desk') {
                const myDeskChair = this.chairRegistry.get('desk_' + roleKey);
                if (myDeskChair) {
                    myDeskChair.occupiedBy = roleKey;
                    return myDeskChair;
                }
            }

            const availableChairs = [];
            for (const chair of this.chairRegistry.values()) {
                if (chair.facility === facilityType && (chair.occupiedBy === null || chair.occupiedBy === roleKey)) {
                    availableChairs.push(chair);
                }
            }

            if (availableChairs.length === 0) {
                return null; // All chairs in this facility are occupied
            }

            // Pick randomly or nearest from available unoccupied chairs
            const chosen = availableChairs[Math.floor(Math.random() * availableChairs.length)];
            chosen.occupiedBy = roleKey;
            return chosen;
        }

        releaseSeat(roleKey) {
            if (!this.chairRegistry) return;
            for (const chair of this.chairRegistry.values()) {
                if (chair.occupiedBy === roleKey) {
                    chair.occupiedBy = null;
                }
            }
        }

        getAgentSeat(roleKey) {
            if (!this.chairRegistry) return null;
            for (const chair of this.chairRegistry.values()) {
                if (chair.occupiedBy === roleKey) {
                    return chair;
                }
            }
            return null;
        }

        init() {
            const width = this.container.clientWidth || 900;
            const height = this.container.clientHeight || 640;

            // 1. Scene
            this.scene = new THREE.Scene();
            this.updateSceneBackground();

            // 2. Camera (Spacious isometric perspective for the 1-floor campus)
            this.camera = new THREE.PerspectiveCamera(40, width / height, 0.1, 300);
            this.setCameraInitialPosition();

            // 3. Renderer with Soft Shadows
            try {
                this.renderer = new THREE.WebGLRenderer({
                    antialias: true,
                    alpha: true,
                    powerPreference: 'high-performance'
                });
            } catch (err) {
                this.renderer = new THREE.WebGLRenderer({ antialias: false, alpha: true });
            }
            this.renderer.setSize(width, height);
            this.renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
            this.renderer.domElement.style.maxWidth = '100%';
            this.renderer.domElement.style.maxHeight = '100%';
            this.renderer.domElement.style.display = 'block';
            this.renderer.shadowMap.enabled = true;
            this.renderer.shadowMap.type = THREE.PCFSoftShadowMap;
            this.renderer.toneMapping = THREE.ACESFilmicToneMapping;
            this.renderer.toneMappingExposure = 1.1;

            this.container.innerHTML = '';
            this.container.appendChild(this.renderer.domElement);

            // 4. OrbitControls
            this.controls = new THREE.OrbitControls(this.camera, this.renderer.domElement);
            this.controls.enableDamping = true;
            this.controls.dampingFactor = 0.06;
            this.controls.maxPolarAngle = Math.PI / 2.1;
            this.controls.minDistance = 8;
            this.controls.maxDistance = 100;
            this.controls.target.set(0, 0, -1);

            // 5. Lighting & Environments
            this.setupLighting();
            this.buildOneFloorCampusArchitecture();
            this.buildAllCampusFacilities();
            this.initChairRegistry();
            this.buildAllTeamWorkstations();
            this.buildArchitecturalOfficeLighting();

            // 6. Register Event Listeners
            window.addEventListener('resize', this._onResize);
            window.addEventListener('keydown', this._onKeyDown);
            window.addEventListener('keyup', this._onKeyUp);
            this.renderer.domElement.addEventListener('mousemove', this._onPointerMove);
            this.renderer.domElement.addEventListener('click', this._onPointerClick);
            this.renderer.domElement.addEventListener('mousedown', this._onMouseDown);
            window.addEventListener('mouseup', this._onMouseUp);

            // Reactive Theme Sync (System UI Standard)
            if (typeof window !== 'undefined') {
                this._onThemeChanged = (e) => {
                    const isDark = e.detail?.isDark ?? document.documentElement.classList.contains('dark');
                    if (this.isNight !== isDark) {
                        this.setDayNight(isDark);
                    }
                };
                window.addEventListener('cooca-theme-changed', this._onThemeChanged);

                if (typeof MutationObserver !== 'undefined' && document.documentElement) {
                    this._themeObserver = new MutationObserver(() => {
                        const isDark = document.documentElement.classList.contains('dark');
                        if (this.isNight !== isDark) {
                            this.setDayNight(isDark);
                        }
                    });
                    this._themeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['class', 'data-theme'] });
                }
            }

            // 7. Initialize Autonomous Sims Lifecycle Routine
            this.initSimsAutonomy();

            // 8. Start Render Loop
            this.animate();
        }

        updateSceneBackground() {
            if (this.isNight) {
                // Deep nocturnal midnight metropolitan sky
                this.scene.background = new THREE.Color(0x070b14);
                this.scene.fog = new THREE.FogExp2(0x070b14, 0.0036);
            } else {
                // Majestic daytime mountain horizon sky with soft atmospheric azure haze
                this.scene.background = new THREE.Color(0x9cc3ec);
                this.scene.fog = new THREE.FogExp2(0xa5cbf3, 0.0026);
            }
        }

        setCameraInitialPosition() {
            if (this.mode === 'executive') {
                this.camera.position.set(0, 16, 2);
                this.camera.lookAt(0, 0, -11);
                if (this.controls) this.controls.target.set(0, 0, -11);
            } else if (this.mode === 'growth') {
                this.camera.position.set(-14, 16, 12);
                this.camera.lookAt(-15, 0, 0);
                if (this.controls) this.controls.target.set(-15, 0, 0);
            } else if (this.mode === 'operations') {
                this.camera.position.set(14, 16, 12);
                this.camera.lookAt(15, 0, 0);
                if (this.controls) this.controls.target.set(15, 0, 0);
            } else {
                // Overview of entire 1-floor campus
                this.camera.position.set(0, 32, 28);
                this.camera.lookAt(0, 0, -1);
                if (this.controls) this.controls.target.set(0, 0, -1);
            }
        }

        setupLighting() {
            // Ambient light: in night mode, deep slate-blue fill; in day mode, clean neutral daylight
            this.ambientLight = new THREE.AmbientLight(
                this.isNight ? 0x1e293b : 0xffffff,
                this.isNight ? 0.60 : 1.05
            );
            this.scene.add(this.ambientLight);

            // Directional Moonlight (night) / Sunlight (day) with soft shadows
            this.dirLight = new THREE.DirectionalLight(
                this.isNight ? 0x93c5fd : 0xfffaed,
                this.isNight ? 0.45 : 1.45
            );
            this.dirLight.position.set(30, 42, 30);
            this.dirLight.castShadow = true;
            this.dirLight.shadow.mapSize.width = 2048;
            this.dirLight.shadow.mapSize.height = 2048;
            this.dirLight.shadow.camera.near = 1.0;
            this.dirLight.shadow.camera.far = 130;
            const d = 36;
            this.dirLight.shadow.camera.left = -d;
            this.dirLight.shadow.camera.right = d;
            this.dirLight.shadow.camera.top = d;
            this.dirLight.shadow.camera.bottom = -d;
            this.dirLight.shadow.bias = -0.0004;
            this.scene.add(this.dirLight);

            // Clear any existing pointlights if re-invoked
            this.pointLights = [];

            // 11 Strategic Architectural Point Lights across all office wings and zones:
            const lightConfigs = [
                // 1. Executive Boardroom: Warm Gold/Amber chandelier glow above boardroom table
                { name: 'boardroom', pos: [0, 4.2, -7.5], color: 0xf59e0b, nightInt: 1.6, dayInt: 0.45, dist: 16 },
                // 2. Executive Desks Suite (CEO, CFO, BI Leads): Warm Ivory task & architectural glow
                { name: 'exec_desks', pos: [0, 4.3, -12.5], color: 0xffeedd, nightInt: 1.5, dayInt: 0.45, dist: 18 },
                // 3. Marketing Creative Studio: Vibrant Creative Violet/Magenta glow
                { name: 'marketing', pos: [-16.0, 4.2, -11.5], color: 0xa855f7, nightInt: 1.6, dayInt: 0.45, dist: 18 },
                // 4. Sales & Customer Success Workbench: Dynamic Electric Cyan glow
                { name: 'sales', pos: [-16.0, 4.2, -1.8], color: 0x38bdf8, nightInt: 1.5, dayInt: 0.45, dist: 16 },
                // 5. West Wing Meeting Conference Room: Warm Golden Chandelier illumination
                { name: 'meeting_room', pos: [-16.0, 4.2, 6.5], color: 0xfbbf24, nightInt: 1.7, dayInt: 0.45, dist: 16 },
                // 6. Central Atrium Rotunda & Biophilic Tree: Crisp Sky Blue / Atrium illumination
                { name: 'atrium_tree', pos: [0, 4.3, 0.5], color: 0x38bdf8, nightInt: 1.5, dayInt: 0.40, dist: 18 },
                // 7. Main Campus Entrance & Reception: Welcoming Champagne/Gold lobby glow
                { name: 'reception', pos: [0, 4.2, 7.5], color: 0xf59e0b, nightInt: 1.6, dayInt: 0.45, dist: 16 },
                // 8. Operations & Logistics Command Hub: High-Tech Emerald/Mint glow
                { name: 'operations', pos: [13.5, 4.2, -11.5], color: 0x10b981, nightInt: 1.6, dayInt: 0.45, dist: 18 },
                // 9. Server Room & IT Support Bay: Cyber Neon Cyan tech glow
                { name: 'server_bay', pos: [21.25, 4.2, -10.0], color: 0x00f0ff, nightInt: 1.5, dayInt: 0.40, dist: 14 },
                // 10. Finance & HR Hub: Elegant Warm Brass/Gold illumination
                { name: 'finance_hr', pos: [12.5, 4.2, 1.5], color: 0xfde68a, nightInt: 1.5, dayInt: 0.45, dist: 16 },
                // 11. Pantry & Breakroom Cafe: Cozy Warm Amber cafe bistro glow
                { name: 'pantry_cafe', pos: [20.5, 4.1, 4.25], color: 0xf59e0b, nightInt: 1.6, dayInt: 0.45, dist: 16 }
            ];

            lightConfigs.forEach(cfg => {
                const light = new THREE.PointLight(
                    cfg.color,
                    this.isNight ? cfg.nightInt : cfg.dayInt,
                    cfg.dist,
                    1.4
                );
                light.defaultNightIntensity = cfg.nightInt;
                light.defaultDayIntensity = cfg.dayInt;
                light.position.set(cfg.pos[0], cfg.pos[1], cfg.pos[2]);
                this.scene.add(light);
                this.pointLights.push(light);
            });
        }

        setDayNight(isNight) {
            this.isNight = !!isNight;
            this.updateSceneBackground();
            if (this.ambientLight) {
                this.ambientLight.color.setHex(this.isNight ? 0x1e293b : 0xffffff);
                this.ambientLight.intensity = this.isNight ? 0.60 : 1.05;
            }
            if (this.dirLight) {
                this.dirLight.color.setHex(this.isNight ? 0x93c5fd : 0xfffaed);
                this.dirLight.intensity = this.isNight ? 0.45 : 1.45;
            }
            if (this.renderer) {
                this.renderer.toneMappingExposure = this.isNight ? 1.08 : 1.15;
            }
            if (this.skylineBldgMat) {
                this.skylineBldgMat.color.setHex(this.isNight ? 0x0f172a : 0xcfd8dc);
            }
            if (this.skylineGlassMat) {
                this.skylineGlassMat.color.setHex(this.isNight ? 0x1e293b : 0xb0bec5);
            }
            if (this.skylineWindowMat) {
                this.skylineWindowMat.color.setHex(this.isNight ? 0xfef08a : 0x90caf9);
            }
            if (this.mountainMat) {
                this.mountainMat.color.setHex(this.isNight ? 0x0c172e : 0x3d6182);
            }
            if (this.mountainRidgeMat) {
                this.mountainRidgeMat.color.setHex(this.isNight ? 0x1d2d44 : 0x7e9ebc);
                if (this.mountainRidgeMat.emissive) {
                    this.mountainRidgeMat.emissive.setHex(this.isNight ? 0x1e3a8a : 0x000000);
                    this.mountainRidgeMat.emissiveIntensity = this.isNight ? 0.35 : 0;
                }
            }
            if (this.mountainHazeMat) {
                this.mountainHazeMat.color.setHex(this.isNight ? 0x070b14 : 0xcbe0f7);
                this.mountainHazeMat.opacity = this.isNight ? 0.45 : 0.35;
            }
            if (this.trafficHeadlightMat) {
                this.trafficHeadlightMat.color.setHex(this.isNight ? 0xffffff : 0xcbd5e1);
            }
            if (this.trafficTaillightMat) {
                this.trafficTaillightMat.color.setHex(this.isNight ? 0xef4444 : 0xf87171);
            }
            if (this.towerFacadeMat) {
                this.towerFacadeMat.color.setHex(this.isNight ? 0x0b1120 : 0x1e293b);
            }
            if (this.towerWindowsMat) {
                this.towerWindowsMat.color.setHex(this.isNight ? 0xfef08a : 0x93c5fd);
                if (this.towerWindowsMat.emissive) {
                    this.towerWindowsMat.emissiveIntensity = this.isNight ? 1.4 : 0.2;
                }
            }
            this.pointLights.forEach(light => {
                light.intensity = this.isNight ? (light.defaultNightIntensity || 1.5) : (light.defaultDayIntensity || 0.45);
            });
            this.roomSignMeshes.forEach(sign => {
                if (sign.material && sign.material.emissive) {
                    sign.material.emissiveIntensity = this.isNight ? 1.2 : 0.4;
                }
            });
            this.officeLampMaterials.forEach(mat => {
                mat.emissiveIntensity = this.isNight ? 2.6 : 0.4;
            });
            this.deskLampMaterials.forEach(mat => {
                mat.emissiveIntensity = this.isNight ? 2.2 : 0.35;
            });
        }

        // =========================================================================
        // ARCHITECTURAL OFFICE LIGHTING & CEILING LUMINAIRE FIXTURES
        // High-end suspended chandeliers, linear pendants, halo rings & cafe fixtures
        // =========================================================================
        buildArchitecturalOfficeLighting() {
            const lightingGroup = new THREE.Group();
            lightingGroup.name = 'architectural_lighting_fixtures';

            // Common Fixture Materials
            const housingMat = new THREE.MeshStandardMaterial({
                color: 0x0f172a,
                roughness: 0.35,
                metalness: 0.85
            });
            const brassMat = new THREE.MeshStandardMaterial({
                color: 0xd97706,
                roughness: 0.25,
                metalness: 0.90
            });
            const wireMat = new THREE.MeshBasicMaterial({ color: 0x475569 });

            // Helper to register an emissive lens material
            const registerLampLensMat = (colorHex, emissiveHex, nightInt = 2.6, dayInt = 0.4) => {
                const mat = new THREE.MeshStandardMaterial({
                    color: colorHex,
                    emissive: emissiveHex,
                    emissiveIntensity: this.isNight ? nightInt : dayInt,
                    roughness: 0.15,
                    metalness: 0.1
                });
                this.officeLampMaterials.push(mat);
                return mat;
            };

            // Helper to add thin vertical steel suspension cables from ceiling (y=4.85) to fixture
            const addSuspensionCable = (x, z, yBottom, yTop = 4.85) => {
                const len = yTop - yBottom;
                if (len <= 0) return;
                const wire = new THREE.Mesh(new THREE.CylinderGeometry(0.005, 0.005, len, 8), wireMat);
                wire.position.set(x, yBottom + len / 2, z);
                lightingGroup.add(wire);
                // Tiny ceiling rosette mount
                const rosette = new THREE.Mesh(new THREE.CylinderGeometry(0.025, 0.025, 0.02, 12), housingMat);
                rosette.position.set(x, yTop, z);
                lightingGroup.add(rosette);
            };

            // ---------------------------------------------------------------------
            // 1. EXECUTIVE BOARDROOM: 4.4m Linear Suspension Luminaire with Brass Accents
            // ---------------------------------------------------------------------
            const boardroomLensMat = registerLampLensMat(0xfef08a, 0xf59e0b, 2.7, 0.45);
            // Black anodized aluminum spine
            const bdHousing = new THREE.Mesh(new THREE.BoxGeometry(0.24, 0.08, 4.4), housingMat);
            bdHousing.position.set(0, 4.30, -7.5);
            lightingGroup.add(bdHousing);

            // Brass accent bevel rails
            const bdBrasTrimL = new THREE.Mesh(new THREE.BoxGeometry(0.02, 0.06, 4.42), brassMat);
            bdBrasTrimL.position.set(-0.12, 4.30, -7.5);
            lightingGroup.add(bdBrasTrimL);
            const bdBrasTrimR = new THREE.Mesh(new THREE.BoxGeometry(0.02, 0.06, 4.42), brassMat);
            bdBrasTrimR.position.set(0.12, 4.30, -7.5);
            lightingGroup.add(bdBrasTrimR);

            // Downward glowing frosted acrylic diffuser lens
            const bdDiffuser = new THREE.Mesh(new THREE.BoxGeometry(0.20, 0.02, 4.36), boardroomLensMat);
            bdDiffuser.position.set(0, 4.25, -7.5);
            lightingGroup.add(bdDiffuser);

            // 4 Steel Suspension Cables
            [-9.2, -8.1, -6.9, -5.8].forEach(z => addSuspensionCable(0, z, 4.34));

            // ---------------------------------------------------------------------
            // 2. EXECUTIVE DESKS SUITE: Architectural Linear Track with Downlight Pods
            // ---------------------------------------------------------------------
            const execTrackLensMat = registerLampLensMat(0xffedd5, 0xffeedd, 2.5, 0.4);
            const execTrackRail = new THREE.Mesh(new THREE.BoxGeometry(11.2, 0.05, 0.06), housingMat);
            execTrackRail.position.set(0, 4.60, -12.5);
            lightingGroup.add(execTrackRail);

            [-5.0, -1.8, 1.8, 5.0].forEach(x => addSuspensionCable(x, -12.5, 4.625));

            // 6 Directional Downlight Pods
            [-4.5, -2.6, -0.6, 0.6, 2.6, 4.5].forEach(x => {
                const pod = new THREE.Mesh(new THREE.CylinderGeometry(0.065, 0.065, 0.16, 16), housingMat);
                pod.position.set(x, 4.50, -12.5);
                lightingGroup.add(pod);

                const podBrassRim = new THREE.Mesh(new THREE.CylinderGeometry(0.07, 0.07, 0.02, 16), brassMat);
                podBrassRim.position.set(x, 4.41, -12.5);
                lightingGroup.add(podBrassRim);

                const podLens = new THREE.Mesh(new THREE.CylinderGeometry(0.055, 0.055, 0.015, 16), execTrackLensMat);
                podLens.position.set(x, 4.40, -12.5);
                lightingGroup.add(podLens);
            });

            // ---------------------------------------------------------------------
            // 3. MARKETING STUDIO: Concentric Twin Halo Ring Pendants (Creative Violet)
            // ---------------------------------------------------------------------
            const mktHaloOuterMat = registerLampLensMat(0xddd6fe, 0xc084fc, 2.7, 0.45);
            const mktHaloInnerMat = registerLampLensMat(0xfbcfe8, 0xf472b6, 2.7, 0.45);

            // Outer Ring (Radius 2.3m)
            const mktOuterRing = new THREE.Mesh(new THREE.TorusGeometry(2.3, 0.045, 16, 48), mktHaloOuterMat);
            mktOuterRing.rotation.x = Math.PI / 2;
            mktOuterRing.position.set(-16.0, 4.15, -11.5);
            lightingGroup.add(mktOuterRing);

            // Inner Ring (Radius 1.3m) slightly higher
            const mktInnerRing = new THREE.Mesh(new THREE.TorusGeometry(1.3, 0.038, 16, 48), mktHaloInnerMat);
            mktInnerRing.rotation.x = Math.PI / 2;
            mktInnerRing.position.set(-16.0, 4.30, -11.5);
            lightingGroup.add(mktInnerRing);

            // Suspension cables for Halo Rings
            [0, Math.PI / 2, Math.PI, (3 * Math.PI) / 2].forEach(angle => {
                const ox = -16.0 + Math.cos(angle) * 2.3;
                const oz = -11.5 + Math.sin(angle) * 2.3;
                addSuspensionCable(ox, oz, 4.18);
            });
            [Math.PI / 4, (3 * Math.PI) / 4, (5 * Math.PI) / 4, (7 * Math.PI) / 4].forEach(angle => {
                const ix = -16.0 + Math.cos(angle) * 1.3;
                const iz = -11.5 + Math.sin(angle) * 1.3;
                addSuspensionCable(ix, iz, 4.32);
            });

            // ---------------------------------------------------------------------
            // 4. SALES WORKBENCH: 8.4m Sleek Linear LED Pendant (Sky Blue)
            // ---------------------------------------------------------------------
            const salesLensMat = registerLampLensMat(0xbae6fd, 0x38bdf8, 2.6, 0.4);
            const salesHousing = new THREE.Mesh(new THREE.BoxGeometry(0.18, 0.08, 8.4), housingMat);
            salesHousing.position.set(-16.0, 4.25, -1.8);
            lightingGroup.add(salesHousing);

            const salesDiffuser = new THREE.Mesh(new THREE.BoxGeometry(0.14, 0.02, 8.34), salesLensMat);
            salesDiffuser.position.set(-16.0, 4.20, -1.8);
            lightingGroup.add(salesDiffuser);

            [-5.2, -2.9, -0.7, 1.6].forEach(z => addSuspensionCable(-16.0, z, 4.29));

            // ---------------------------------------------------------------------
            // 5. MEETING CONFERENCE ROOM: Warm Amber Halo Ring Chandelier
            // ---------------------------------------------------------------------
            const meetingHaloMat = registerLampLensMat(0xfef08a, 0xfde047, 2.8, 0.45);
            const meetingRing = new THREE.Mesh(new THREE.TorusGeometry(1.85, 0.055, 16, 48), meetingHaloMat);
            meetingRing.rotation.x = Math.PI / 2;
            meetingRing.position.set(-16.0, 4.15, 6.5);
            lightingGroup.add(meetingRing);

            // Architectural central rosette & 4 diagonal cables
            const meetingRosette = new THREE.Mesh(new THREE.CylinderGeometry(0.16, 0.16, 0.04, 20), housingMat);
            meetingRosette.position.set(-16.0, 4.85, 6.5);
            lightingGroup.add(meetingRosette);

            [0, Math.PI / 2, Math.PI, (3 * Math.PI) / 2].forEach(angle => {
                const rx = -16.0 + Math.cos(angle) * 1.85;
                const rz = 6.5 + Math.sin(angle) * 1.85;
                addSuspensionCable(rx, rz, 4.18);
            });

            // ---------------------------------------------------------------------
            // 6. CENTRAL ROTUNDA & BIOPHILIC TREE: Grand 3.6m Circular Crown Chandelier
            // ---------------------------------------------------------------------
            const atriumCrownMat = registerLampLensMat(0xbae6fd, 0x38bdf8, 2.6, 0.4);
            const crownRing = new THREE.Mesh(new THREE.TorusGeometry(3.6, 0.075, 16, 64), atriumCrownMat);
            crownRing.rotation.x = Math.PI / 2;
            crownRing.position.set(0, 4.35, 0.5);
            lightingGroup.add(crownRing);

            // Outer black housing shell for the crown
            const crownHousing = new THREE.Mesh(new THREE.TorusGeometry(3.6, 0.082, 8, 64), housingMat);
            crownHousing.rotation.x = Math.PI / 2;
            crownHousing.position.set(0, 4.39, 0.5);
            lightingGroup.add(crownHousing);

            // 8 Radial suspension wires connecting crown to structural rafters
            for (let i = 0; i < 8; i++) {
                const angle = (i * Math.PI * 2) / 8;
                const cx = Math.cos(angle) * 3.6;
                const cz = 0.5 + Math.sin(angle) * 3.6;
                addSuspensionCable(cx, cz, 4.40);
            }

            // ---------------------------------------------------------------------
            // 7. MAIN ENTRANCE & RECEPTION: Geometric Modern Canopy Bars (Warm Gold)
            // ---------------------------------------------------------------------
            const receptionLensMat = registerLampLensMat(0xfef3c7, 0xfbbf24, 2.6, 0.45);
            const recBars = [
                { x: -0.7, y: 4.30, z: 6.8, rotY: 0.18, len: 2.8 },
                { x: 0.7, y: 4.22, z: 7.7, rotY: -0.16, len: 2.8 },
                { x: 0.0, y: 4.40, z: 8.6, rotY: 0.04, len: 2.2 }
            ];
            recBars.forEach(b => {
                const barGroup = new THREE.Group();
                barGroup.position.set(b.x, b.y, b.z);
                barGroup.rotation.y = b.rotY;

                const housing = new THREE.Mesh(new THREE.BoxGeometry(0.12, 0.06, b.len), housingMat);
                barGroup.add(housing);

                const diffuser = new THREE.Mesh(new THREE.BoxGeometry(0.09, 0.015, b.len - 0.04), receptionLensMat);
                diffuser.position.y = -0.035;
                barGroup.add(diffuser);

                lightingGroup.add(barGroup);

                // Suspension cables at both ends
                const halfLen = (b.len / 2) - 0.2;
                const cosR = Math.cos(b.rotY);
                const sinR = Math.sin(b.rotY);
                addSuspensionCable(b.x - sinR * halfLen, b.z - cosR * halfLen, b.y + 0.03);
                addSuspensionCable(b.x + sinR * halfLen, b.z + cosR * halfLen, b.y + 0.03);
            });

            // ---------------------------------------------------------------------
            // 8. OPERATIONS COMMAND HUB: High-Tech Dual-Truss Luminaire (Emerald Green)
            // ---------------------------------------------------------------------
            const opsLensMat = registerLampLensMat(0xa7f3d0, 0x34d399, 2.7, 0.4);
            const opsHousing = new THREE.Mesh(new THREE.BoxGeometry(9.6, 0.08, 0.22), housingMat);
            opsHousing.position.set(13.5, 4.25, -11.5);
            lightingGroup.add(opsHousing);

            const opsDiffuser = new THREE.Mesh(new THREE.BoxGeometry(9.52, 0.02, 0.16), opsLensMat);
            opsDiffuser.position.set(13.5, 4.20, -11.5);
            lightingGroup.add(opsDiffuser);

            [9.5, 12.0, 15.0, 17.5].forEach(x => addSuspensionCable(x, -11.5, 4.29));

            // ---------------------------------------------------------------------
            // 9. SERVER ROOM & IT BAY: Cyberpunk Linear Luminaire Strip (Neon Cyan)
            // ---------------------------------------------------------------------
            const serverLensMat = registerLampLensMat(0xa5f3fc, 0x00f0ff, 2.8, 0.4);
            const serverHousing = new THREE.Mesh(new THREE.BoxGeometry(0.14, 0.06, 4.6), housingMat);
            serverHousing.position.set(21.25, 4.30, -10.0);
            lightingGroup.add(serverHousing);

            const serverDiffuser = new THREE.Mesh(new THREE.BoxGeometry(0.10, 0.02, 4.54), serverLensMat);
            serverDiffuser.position.set(21.25, 4.26, -10.0);
            lightingGroup.add(serverDiffuser);

            [-11.8, -10.0, -8.2].forEach(z => addSuspensionCable(21.25, z, 4.33));

            // ---------------------------------------------------------------------
            // 10. FINANCE & HR HUB: Dual Sleek Linear Luminaires (Champagne Gold)
            // ---------------------------------------------------------------------
            const finHrLensMat = registerLampLensMat(0xfef3c7, 0xfde68a, 2.5, 0.4);

            // Finance Bar
            const finHousing = new THREE.Mesh(new THREE.BoxGeometry(5.2, 0.07, 0.16), housingMat);
            finHousing.position.set(13.0, 4.30, -2.5);
            lightingGroup.add(finHousing);
            const finDiffuser = new THREE.Mesh(new THREE.BoxGeometry(5.12, 0.02, 0.12), finHrLensMat);
            finDiffuser.position.set(13.0, 4.26, -2.5);
            lightingGroup.add(finDiffuser);
            [11.0, 15.0].forEach(x => addSuspensionCable(x, -2.5, 4.34));

            // HR Bar
            const hrHousing = new THREE.Mesh(new THREE.BoxGeometry(4.2, 0.07, 0.16), housingMat);
            hrHousing.position.set(11.5, 4.30, 3.5);
            lightingGroup.add(hrHousing);
            const hrDiffuser = new THREE.Mesh(new THREE.BoxGeometry(4.12, 0.02, 0.12), finHrLensMat);
            hrDiffuser.position.set(11.5, 4.26, 3.5);
            lightingGroup.add(hrDiffuser);
            [9.8, 13.2].forEach(x => addSuspensionCable(x, 3.5, 4.34));

            // ---------------------------------------------------------------------
            // 11. PANTRY & CAFE BISTRO: 3 Scandinavian Hanging Pendant Lamps with Edison Bulbs
            // ---------------------------------------------------------------------
            const cafeBulbMat = registerLampLensMat(0xfde68a, 0xf59e0b, 3.0, 0.5);
            const cafePendantCoords = [
                { x: 20.0, z: 3.2 },
                { x: 20.0, z: 4.3 },
                { x: 20.0, z: 5.4 }
            ];

            cafePendantCoords.forEach(pos => {
                // Ceiling rosette
                const rosette = new THREE.Mesh(new THREE.CylinderGeometry(0.06, 0.06, 0.03, 16), housingMat);
                rosette.position.set(pos.x, 4.85, pos.z);
                lightingGroup.add(rosette);

                // Hanging cord from y=4.85 to y=3.75 (length 1.1m)
                const cord = new THREE.Mesh(new THREE.CylinderGeometry(0.006, 0.006, 1.1, 8), housingMat);
                cord.position.set(pos.x, 4.30, pos.z);
                lightingGroup.add(cord);

                // Spun-metal cone/bell shade in matte black with brass accent ring
                const brassSocket = new THREE.Mesh(new THREE.CylinderGeometry(0.035, 0.035, 0.06, 16), brassMat);
                brassSocket.position.set(pos.x, 3.76, pos.z);
                lightingGroup.add(brassSocket);

                const shade = new THREE.Mesh(new THREE.CylinderGeometry(0.06, 0.28, 0.18, 24, 1, true), housingMat);
                shade.position.set(pos.x, 3.66, pos.z);
                lightingGroup.add(shade);

                // Warm glowing Edison bulb peeking slightly below shade
                const bulb = new THREE.Mesh(new THREE.SphereGeometry(0.08, 16, 16), cafeBulbMat);
                bulb.position.set(pos.x, 3.60, pos.z);
                lightingGroup.add(bulb);
            });

            this.scene.add(lightingGroup);
        }

        // ==========================================
        // 1. 1-FLOOR ARCHITECTURAL CAMPUS BUILDING
        // ==========================================
        buildOneFloorCampusArchitecture() {
            const campusGroup = new THREE.Group();

            // Materials
            const slabMat = new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.7 });
            const darkPavementMat = new THREE.MeshStandardMaterial({ color: 0x0f172a, roughness: 0.9 });
            const wallMat = new THREE.MeshStandardMaterial({ color: 0x1e2430, roughness: 0.5 });
            const pillarMat = new THREE.MeshStandardMaterial({ color: 0x0f172a, roughness: 0.3 });

            // ========================================================
            // 1. COOCA TOWER: LEVEL 20 TOP FLOOR PENTHOUSE SUPERSTRUCTURE
            // ========================================================
            // A. Skyscraper Tower Shaft below (Floors 1 to 19 extending down to y = -55)
            this.buildSkyscraperTowerShaft(campusGroup);

            // B. Level 20 Penthouse Floor Slab
            const penthouseSlab = new THREE.Mesh(new THREE.BoxGeometry(53, 0.5, 29), darkPavementMat);
            penthouseSlab.position.set(0, -0.25, -2.5);
            penthouseSlab.receiveShadow = true;
            campusGroup.add(penthouseSlab);

            // C. Rooftop Penthouse Sky Terrace & Observation Deck (Z = 11.0 to 24.5)
            this.buildRooftopSkyTerrace(campusGroup);

            // ========================================================
            // 2. PERIMETER PANORAMIC GLASS CURTAIN WALLS (FLOOR-TO-CEILING)
            // Building Envelope: X: [-25.0, 25.0] (50m wide), Z: [-16.0, 11.0] (27m deep)
            // Gives breathtaking 360-degree metropolitan & mountain views!
            // ========================================================
            // A. North Facade (z = -16.0): Panoramic Glass Curtain Wall facing Mountain Range
            this.buildPanoramicCurtainWall(campusGroup, 0, -16.0, 50.5, 0.4, 4.8, 'x');

            // B. West Facade (x = -25.0): Panoramic Glass Curtain Wall facing West Financial Skyline
            this.buildPanoramicCurtainWall(campusGroup, -25.0, -2.5, 0.4, 27.5, 4.8, 'z');

            // C. East Facade (x = 25.0): Panoramic Glass Curtain Wall facing East Commercial Skyline
            this.buildPanoramicCurtainWall(campusGroup, 25.0, -2.5, 0.4, 27.5, 4.8, 'z');

            // D. South Facade (z = 11.0): Penthouse Terrace Glass Entrance & Sliding Doors
            this.buildSouthExteriorFacade(campusGroup);

            // E. Structural Columns / Architectural Pillars along Perimeter
            [-25.0, -15.25, -4.5, 4.5, 15.25, 25.0].forEach(cx => {
                const col = new THREE.Mesh(new THREE.BoxGeometry(0.7, 5.1, 0.7), pillarMat);
                col.position.set(cx, 2.55, 11.0);
                campusGroup.add(col);
            });
            [-25.0, -17.0, -8.5, 0, 8.5, 17.0, 25.0].forEach(cx => {
                const col = new THREE.Mesh(new THREE.BoxGeometry(0.7, 5.1, 0.7), pillarMat);
                col.position.set(cx, 2.55, -16.0);
                campusGroup.add(col);
            });

            // ========================================================
            // 3. 360° METROPOLITAN SURROUNDING SKYLINE & FAR HORIZON MOUNTAIN RANGE
            // ========================================================
            this.build360CitySkyline(campusGroup);
            this.buildDistantMountainRange(campusGroup);

            // ========================================================
            // 3. BANGUN RUANGAN-RUANGAN DI DALAMNYA (INTERIOR FLOOR SLABS)
            // 100% MATCH DENGAN DENAH LOKASI 2D BLUEPRINT SVG (13 DISTINCT ROOMS)
            // ========================================================
            // 1. Central Spine:
            // A. Executive Suite (Top-Center: Rich Dark Walnut Parquet)
            this.createRoomFloorTile(campusGroup, 0, -9.975, 14.4, 11.55, 0x271e18, 0.25, 0.15);
            // B. Central Atrium & Rotunda (Center: Polished White Terrazzo Marble)
            this.createRoomFloorTile(campusGroup, 0, 0.65, 14.4, 9.7, 0xf8fafc, 0.18, 0.12);
            // C. Main Entrance & Reception (Bottom-Center: Granite Entrance Slab)
            this.createRoomFloorTile(campusGroup, 0, 8.125, 14.4, 5.25, 0x1e293b, 0.35, 0.1);

            // 2. West Wing:
            // D. Marketing Room (Top-West: Scandinavian Light Blonde Oak)
            this.createRoomFloorTile(campusGroup, -15.975, -10.375, 17.55, 10.75, 0xe5ded3, 0.3, 0.08);
            // E. Sales Room (Mid-West: Modern Warm Maple Wood)
            this.createRoomFloorTile(campusGroup, -15.975, -1.25, 17.55, 7.5, 0xddceb9, 0.32, 0.08);
            // F. Meeting Room (Bottom-West: Acoustic Midnight Navy Carpet)
            this.createRoomFloorTile(campusGroup, -15.975, 6.625, 17.55, 8.25, 0x1e3a8a, 0.65, 0.04);

            // 3. East Wing:
            // G. Operations Room (Top-East Mid: Industrial Dark Slate)
            this.createRoomFloorTile(campusGroup, 12.6, -10.375, 10.8, 10.75, 0x1e293b, 0.45, 0.18);
            // H. Server Room & IT Support (Far Top-Right: Anti-Static Raised Navy Floor)
            this.createRoomFloorTile(campusGroup, 21.375, -10.375, 6.75, 10.75, 0x0a1120, 0.5, 0.25);
            // I. Finance Room (Mid-East Mid: Graphite Executive Weave)
            this.createRoomFloorTile(campusGroup, 12.6, -1.75, 10.8, 6.5, 0x2d3748, 0.4, 0.1);
            // J. Document Archive (Far Mid-Right: Durable Concrete Archive Tile)
            this.createRoomFloorTile(campusGroup, 21.375, -1.75, 6.75, 6.5, 0x3d2716, 0.55, 0.08);
            // K. Bistro Lounge & Cafe Area (Warm Bistro Walnut Parquet: x=7.2 to 15.5, z=1.5 to 6.8)
            this.createRoomFloorTile(campusGroup, 11.35, 4.15, 8.3, 5.3, 0x3d2716, 0.42, 0.08);
            // L. Pantry & Dining (Lower Mid-East Far: Warm Bistro Porcelain Tile)
            this.createRoomFloorTile(campusGroup, 20.125, 4.15, 9.25, 5.3, 0xf1f5f9, 0.22, 0.12);
            // M. Restroom (Far Bottom-Right: Hygienic Ceramic Tile)
            this.createRoomFloorTile(campusGroup, 15.975, 8.775, 17.55, 3.95, 0xe2e8f0, 0.28, 0.1);

            // 4. Glass Partitions separating Wings & Rooms (100% 2D Matching)
            this.buildOneFloorGlassPartitions(campusGroup);

            // 5. Overhead Illuminated Banners
            this.createOverheadWingSign(campusGroup, 0, 4.4, -4.5, '👑 Executive Suite', 'AI CEO • CFO • Business • Strategy', 0xf59e0b);
            this.createOverheadWingSign(campusGroup, -16.0, 4.4, -5.2, '📈 Growth & Marketing Wing', 'Campaign • Sales • Content • Customer Care', 0xa855f7);
            this.createOverheadWingSign(campusGroup, 16.0, 4.4, -5.2, '⚙️ Operations & Tech Wing', 'Inventory • Supply • IT • Finance • HR', 0x10b981);

            this.scene.add(campusGroup);
        }

        createRoomFloorTile(parent, x, z, width, depth, colorHex, roughness, metalness) {
            const tileMat = new THREE.MeshStandardMaterial({
                color: colorHex,
                roughness: roughness,
                metalness: metalness
            });
            const tile = new THREE.Mesh(new THREE.BoxGeometry(width, 0.02, depth), tileMat);
            tile.position.set(x, 0.01, z);
            tile.receiveShadow = true;
            parent.add(tile);
        }

        buildPanoramicCurtainWall(parent, x, z, width, depth, height, orientation = 'x') {
            const wallGroup = new THREE.Group();
            wallGroup.position.set(x, 0, z);

            const wallMat = new THREE.MeshStandardMaterial({ color: 0x1e2430, roughness: 0.5 });
            const frameMat = new THREE.MeshStandardMaterial({ color: 0x0f172a, roughness: 0.3, metalness: 0.8 });
            const glassMat = new THREE.MeshPhysicalMaterial({
                color: 0x93c5fd,
                transparent: true,
                opacity: 0.32,
                roughness: 0.08,
                transmission: 0.88,
                depthWrite: false
            });

            const kickH = 0.5;
            const spandrelH = 0.5;
            const glassH = height - kickH - spandrelH;
            const glassY = kickH + glassH / 2;

            if (orientation === 'x') {
                // Lower kickplate
                const kick = new THREE.Mesh(new THREE.BoxGeometry(width, kickH, depth), wallMat);
                kick.position.set(0, kickH / 2, 0);
                wallGroup.add(kick);

                // Upper spandrel beam
                const spandrel = new THREE.Mesh(new THREE.BoxGeometry(width, spandrelH, depth), wallMat);
                spandrel.position.set(0, height - spandrelH / 2, 0);
                wallGroup.add(spandrel);

                // Glass curtain wall
                const glass = new THREE.Mesh(new THREE.BoxGeometry(width - 0.2, glassH, 0.08), glassMat);
                glass.position.set(0, glassY, 0);
                wallGroup.add(glass);

                // Vertical architectural mullions every 3.8m
                const halfW = width / 2;
                for (let mx = -halfW + 3.8; mx < halfW; mx += 3.8) {
                    const mullion = new THREE.Mesh(new THREE.BoxGeometry(0.12, glassH, 0.16), frameMat);
                    mullion.position.set(mx, glassY, 0);
                    wallGroup.add(mullion);
                }
            } else {
                // orientation === 'z'
                // Lower kickplate
                const kick = new THREE.Mesh(new THREE.BoxGeometry(width, kickH, depth), wallMat);
                kick.position.set(0, kickH / 2, 0);
                wallGroup.add(kick);

                // Upper spandrel beam
                const spandrel = new THREE.Mesh(new THREE.BoxGeometry(width, spandrelH, depth), wallMat);
                spandrel.position.set(0, height - spandrelH / 2, 0);
                wallGroup.add(spandrel);

                // Glass curtain wall
                const glass = new THREE.Mesh(new THREE.BoxGeometry(0.08, glassH, depth - 0.2), glassMat);
                glass.position.set(0, glassY, 0);
                wallGroup.add(glass);

                // Vertical architectural mullions every 3.8m
                const halfD = depth / 2;
                for (let mz = -halfD + 3.8; mz < halfD; mz += 3.8) {
                    const mullion = new THREE.Mesh(new THREE.BoxGeometry(0.16, glassH, 0.12), frameMat);
                    mullion.position.set(0, glassY, mz);
                    wallGroup.add(mullion);
                }
            }

            parent.add(wallGroup);
        }

        buildPanoramicWindows(parent, width, zPos) {
            // Maintained for backward compatibility
            this.buildPanoramicCurtainWall(parent, 0, zPos, width, 0.4, 4.8, 'x');
        }

        buildCitySkyline(parent, zPos) {
            // Maintained for backward compatibility
            this.build360CitySkyline(parent);
        }

        build360CitySkyline(parent) {
            const cityGroup = new THREE.Group();
            cityGroup.name = 'metropolitan_city_skyline';

            this.skylineBldgMat = new THREE.MeshStandardMaterial({
                color: this.isNight ? 0x0f172a : 0xcfd8dc,
                roughness: 0.75,
                metalness: 0.25
            });

            this.skylineGlassMat = new THREE.MeshStandardMaterial({
                color: this.isNight ? 0x1e293b : 0xb0bec5,
                roughness: 0.2,
                metalness: 0.8
            });

            this.skylineWindowMat = new THREE.MeshBasicMaterial({
                color: this.isNight ? 0xfef08a : 0x90caf9
            });

            this.trafficHeadlightMat = new THREE.MeshBasicMaterial({
                color: this.isNight ? 0xffffff : 0xcbd5e1
            });

            this.trafficTaillightMat = new THREE.MeshBasicMaterial({
                color: this.isNight ? 0xef4444 : 0xf87171
            });

            this.beaconRedMat = new THREE.MeshBasicMaterial({
                color: 0xef4444
            });

            // Metropolitan Ground Plane down below at y = -48
            const groundCity = new THREE.Mesh(
                new THREE.PlaneGeometry(360, 360),
                new THREE.MeshStandardMaterial({ color: this.isNight ? 0x050811 : 0x94a3b8, roughness: 0.95 })
            );
            groundCity.rotation.x = -Math.PI / 2;
            groundCity.position.set(0, -48, 0);
            groundCity.receiveShadow = false;
            cityGroup.add(groundCity);

            // Flowing Street Network & Traffic Light Trails down below at y = -47.8
            this.buildCityStreetsAndTraffic(cityGroup);

            // Helper to generate a modern skyscraper with windows and rooftop details
            const createTower = (x, z, h, w, d, style = 'standard') => {
                const bldgMat = (style === 'glass') ? this.skylineGlassMat : this.skylineBldgMat;
                const totalH = h + 48;
                const bldg = new THREE.Mesh(new THREE.BoxGeometry(w, totalH, d), bldgMat);
                bldg.position.set(x, totalH / 2 - 48, z);
                cityGroup.add(bldg);

                // Window Grids: illuminated floor strips and windows
                const topY = h;
                const winSpacingY = 2.4;
                const winSpacingX = 1.6;

                for (let wy = -35; wy < topY - 3; wy += winSpacingY) {
                    for (let wx = -w / 2 + 1.0; wx <= w / 2 - 1.0; wx += winSpacingX) {
                        if (Math.random() > 0.40) {
                            const win = new THREE.Mesh(new THREE.PlaneGeometry(0.85, 1.1), this.skylineWindowMat);
                            win.position.set(x + wx, wy, z + d / 2 + 0.05);
                            cityGroup.add(win);
                        }
                    }
                }

                // Rooftop Architectural Crown / Antenna
                if (style === 'stepped') {
                    const stepH = 4.5;
                    const step = new THREE.Mesh(new THREE.BoxGeometry(w * 0.7, stepH, d * 0.7), this.skylineBldgMat);
                    step.position.set(x, topY + stepH / 2, z);
                    cityGroup.add(step);
                    this.createAntennaSpire(cityGroup, x, topY + stepH, z, 7.5);
                } else if (style === 'antenna') {
                    this.createAntennaSpire(cityGroup, x, topY, z, 9.0);
                } else {
                    const mech = new THREE.Mesh(new THREE.BoxGeometry(w * 0.5, 2.0, d * 0.5), this.skylineBldgMat);
                    mech.position.set(x, topY + 1.0, z);
                    cityGroup.add(mech);
                }
            };

            // 1. NORTH SECTOR: High-rises framing the Mountain Vista corridor
            createTower(-28, -48, 38, 7.5, 7.5, 'stepped');
            createTower(-42, -55, 46, 8.5, 8.5, 'antenna');
            createTower(-58, -45, 34, 7.0, 7.0, 'standard');
            createTower(-20, -60, 42, 8.0, 8.0, 'glass');
            createTower(-36, -72, 55, 9.0, 9.0, 'stepped');
            // Center is kept open (z = -65, h <= 18) for vista view of mountain peaks!
            createTower(-6, -75, 18, 6.0, 6.0, 'standard');
            createTower(6, -75, 19, 6.5, 6.5, 'standard');
            createTower(22, -60, 40, 7.5, 7.5, 'glass');
            createTower(38, -50, 48, 8.5, 8.5, 'antenna');
            createTower(54, -45, 36, 7.0, 7.0, 'standard');
            createTower(40, -70, 52, 9.0, 9.0, 'stepped');
            createTower(62, -62, 44, 8.0, 8.0, 'glass');

            // 2. WEST SECTOR: Financial District Skyscrapers
            createTower(-52, -22, 45, 8.0, 8.0, 'glass');
            createTower(-65, -8, 52, 9.0, 9.0, 'stepped');
            createTower(-48, 6, 38, 7.5, 7.5, 'antenna');
            createTower(-62, 20, 42, 8.5, 8.5, 'standard');
            createTower(-50, 32, 36, 7.0, 7.0, 'glass');
            createTower(-75, 12, 60, 9.5, 9.5, 'antenna');

            // 3. EAST SECTOR: Commercial District & Supertall Telecom Spire
            createTower(52, -22, 42, 8.0, 8.0, 'standard');
            createTower(64, -8, 48, 8.5, 8.5, 'glass');
            createTower(48, 8, 36, 7.5, 7.5, 'standard');
            createTower(62, 22, 44, 8.0, 8.0, 'stepped');
            createTower(50, 34, 40, 7.0, 7.0, 'glass');
            createTower(75, 4, 68, 8.0, 8.0, 'antenna');

            // 4. SOUTH SECTOR: Downtown City Core (Visible from Sky Terrace)
            createTower(-32, 50, 36, 7.5, 7.5, 'standard');
            createTower(-16, 58, 42, 8.0, 8.0, 'glass');
            createTower(0, 62, 48, 9.0, 9.0, 'stepped');
            createTower(18, 56, 44, 8.5, 8.5, 'antenna');
            createTower(34, 48, 38, 7.5, 7.5, 'standard');
            createTower(-45, 65, 46, 8.5, 8.5, 'glass');
            createTower(45, 65, 50, 9.0, 9.0, 'stepped');

            // Sky Helicopter Patrol
            this.buildSkyHelicopter(cityGroup);

            parent.add(cityGroup);
        }

        buildCityStreetsAndTraffic(parent) {
            const streetGroup = new THREE.Group();
            streetGroup.name = 'city_streets_and_traffic';
            streetGroup.position.set(0, -47.8, 0);

            // Re-initialize vehicle & street NPC collections
            this.cityVehicles = [];
            this.cityNPCs = this.cityNPCs ? this.cityNPCs.filter(npc => npc.isTerrace) : [];

            const isDark = this.isNight;
            const roadMat = new THREE.MeshStandardMaterial({
                color: isDark ? 0x090d16 : 0x272e3b,
                roughness: 0.85
            });
            const sidewalkMat = new THREE.MeshStandardMaterial({
                color: isDark ? 0x1e293b : 0x94a3b8,
                roughness: 0.9
            });
            const curbMat = new THREE.MeshStandardMaterial({
                color: isDark ? 0x334155 : 0x64748b,
                roughness: 0.7
            });
            const whiteMarkingMat = new THREE.MeshBasicMaterial({ color: 0xf8fafc });
            const yellowMarkingMat = new THREE.MeshBasicMaterial({ color: 0xfbbf24 });
            const streetPoleMat = new THREE.MeshStandardMaterial({ color: 0x475569, metalness: 0.85, roughness: 0.3 });
            const streetBulbMat = new THREE.MeshBasicMaterial({ color: isDark ? 0xfef08a : 0xf1f5f9 });
            const treeTrunkMat = new THREE.MeshStandardMaterial({ color: 0x451a03, roughness: 0.9 });
            const treeLeavesMat = new THREE.MeshStandardMaterial({ color: isDark ? 0x064e3b : 0x166534, roughness: 0.8 });
            const busGlassMat = new THREE.MeshStandardMaterial({ color: 0x93c5fd, transparent: true, opacity: 0.45, roughness: 0.1 });

            // 1. Urban Arterial Avenues (North-South & East-West)
            // Road Width = 8.4m (4 lanes: 2 in each direction, 2.1m each lane)
            const avenues = [
                { id: 'north', x: 0, z: -45, w: 280, d: 8.4, dir: 'x' },
                { id: 'south', x: 0, z: 45, w: 280, d: 8.4, dir: 'x' },
                { id: 'west',  x: -48, z: 0, w: 8.4, d: 280, dir: 'z' },
                { id: 'east',  x: 48, z: 0, w: 8.4, d: 280, dir: 'z' }
            ];

            avenues.forEach(av => {
                // Asphalt Pavement
                const road = new THREE.Mesh(new THREE.BoxGeometry(av.w, 0.08, av.d), roadMat);
                road.position.set(av.x, 0.04, av.z);
                streetGroup.add(road);

                // Sidewalks & Curbs on both flanks of the avenue
                if (av.dir === 'x') {
                    // North Flank Sidewalk (z - av.d/2 - 1.8)
                    const swNorth = new THREE.Mesh(new THREE.BoxGeometry(av.w, 0.18, 3.6), sidewalkMat);
                    swNorth.position.set(av.x, 0.09, av.z - av.d / 2 - 1.8);
                    streetGroup.add(swNorth);

                    const curbNorth = new THREE.Mesh(new THREE.BoxGeometry(av.w, 0.22, 0.2), curbMat);
                    curbNorth.position.set(av.x, 0.11, av.z - av.d / 2 - 0.1);
                    streetGroup.add(curbNorth);

                    // South Flank Sidewalk (z + av.d/2 + 1.8)
                    const swSouth = new THREE.Mesh(new THREE.BoxGeometry(av.w, 0.18, 3.6), sidewalkMat);
                    swSouth.position.set(av.x, 0.09, av.z + av.d / 2 + 1.8);
                    streetGroup.add(swSouth);

                    const curbSouth = new THREE.Mesh(new THREE.BoxGeometry(av.w, 0.22, 0.2), curbMat);
                    curbSouth.position.set(av.x, 0.11, av.z + av.d / 2 + 0.1);
                    streetGroup.add(curbSouth);

                    // Road Markings: Solid Double Center Yellow Lines
                    const centerYellow1 = new THREE.Mesh(new THREE.BoxGeometry(av.w, 0.02, 0.14), yellowMarkingMat);
                    centerYellow1.position.set(av.x, 0.09, av.z - 0.12);
                    streetGroup.add(centerYellow1);

                    const centerYellow2 = new THREE.Mesh(new THREE.BoxGeometry(av.w, 0.02, 0.14), yellowMarkingMat);
                    centerYellow2.position.set(av.x, 0.09, av.z + 0.12);
                    streetGroup.add(centerYellow2);

                    // Dashed Lane Dividers (Lane 1/2 and Lane 3/4)
                    for (let sx = -av.w / 2 + 4; sx < av.w / 2; sx += 7.5) {
                        const dashNorth = new THREE.Mesh(new THREE.BoxGeometry(3.2, 0.02, 0.15), whiteMarkingMat);
                        dashNorth.position.set(sx, 0.09, av.z - 2.1);
                        streetGroup.add(dashNorth);

                        const dashSouth = new THREE.Mesh(new THREE.BoxGeometry(3.2, 0.02, 0.15), whiteMarkingMat);
                        dashSouth.position.set(sx, 0.09, av.z + 2.1);
                        streetGroup.add(dashSouth);
                    }
                } else {
                    // West Flank Sidewalk (x - av.w/2 - 1.8)
                    const swWest = new THREE.Mesh(new THREE.BoxGeometry(3.6, 0.18, av.d), sidewalkMat);
                    swWest.position.set(av.x - av.w / 2 - 1.8, 0.09, av.z);
                    streetGroup.add(swWest);

                    const curbWest = new THREE.Mesh(new THREE.BoxGeometry(0.2, 0.22, av.d), curbMat);
                    curbWest.position.set(av.x - av.w / 2 - 0.1, 0.11, av.z);
                    streetGroup.add(curbWest);

                    // East Flank Sidewalk (x + av.w/2 + 1.8)
                    const swEast = new THREE.Mesh(new THREE.BoxGeometry(3.6, 0.18, av.d), sidewalkMat);
                    swEast.position.set(av.x + av.w / 2 + 1.8, 0.09, av.z);
                    streetGroup.add(swEast);

                    const curbEast = new THREE.Mesh(new THREE.BoxGeometry(0.2, 0.22, av.d), curbMat);
                    curbEast.position.set(av.x + av.w / 2 + 0.1, 0.11, av.z);
                    streetGroup.add(curbEast);

                    // Center Double Yellow Line
                    const centerYellow1 = new THREE.Mesh(new THREE.BoxGeometry(0.14, 0.02, av.d), yellowMarkingMat);
                    centerYellow1.position.set(av.x - 0.12, 0.09, av.z);
                    streetGroup.add(centerYellow1);

                    const centerYellow2 = new THREE.Mesh(new THREE.BoxGeometry(0.14, 0.02, av.d), yellowMarkingMat);
                    centerYellow2.position.set(av.x + 0.12, 0.09, av.z);
                    streetGroup.add(centerYellow2);

                    // Dashed Lane Dividers
                    for (let sz = -av.d / 2 + 4; sz < av.d / 2; sz += 7.5) {
                        const dashWest = new THREE.Mesh(new THREE.BoxGeometry(0.15, 0.02, 3.2), whiteMarkingMat);
                        dashWest.position.set(av.x - 2.1, 0.09, sz);
                        streetGroup.add(dashWest);

                        const dashEast = new THREE.Mesh(new THREE.BoxGeometry(0.15, 0.02, 3.2), whiteMarkingMat);
                        dashEast.position.set(av.x + 2.1, 0.09, sz);
                        streetGroup.add(dashEast);
                    }
                }
            });

            // 2. Pedestrian Zebra Crosswalks at Major Intersections (±48, ±45)
            const intersections = [
                { x: -48, z: -45 },
                { x: 48,  z: -45 },
                { x: -48, z: 45 },
                { x: 48,  z: 45 }
            ];

            intersections.forEach(ix => {
                const createZebraCrossing = (cx, cz, isHorizontal) => {
                    const zebraGroup = new THREE.Group();
                    zebraGroup.position.set(cx, 0.095, cz);
                    const barCount = 7;
                    const barLength = 3.6;
                    const barWidth = 0.55;
                    const spacing = 0.95;

                    for (let b = 0; b < barCount; b++) {
                        const offset = (b - (barCount - 1) / 2) * spacing;
                        const bar = new THREE.Mesh(
                            new THREE.BoxGeometry(isHorizontal ? barWidth : barLength, 0.015, isHorizontal ? barLength : barWidth),
                            whiteMarkingMat
                        );
                        if (isHorizontal) bar.position.x = offset;
                        else bar.position.z = offset;
                        zebraGroup.add(bar);
                    }
                    streetGroup.add(zebraGroup);
                };

                createZebraCrossing(ix.x, ix.z - 6.5, true);
                createZebraCrossing(ix.x, ix.z + 6.5, true);
                createZebraCrossing(ix.x - 6.5, ix.z, false);
                createZebraCrossing(ix.x + 6.5, ix.z, false);

                // Modern Architectural Traffic Signal Mast at corner
                const mast = new THREE.Mesh(new THREE.CylinderGeometry(0.08, 0.12, 4.8, 8), streetPoleMat);
                mast.position.set(ix.x + 5.2, 2.4, ix.z + 5.2);
                streetGroup.add(mast);

                const arm = new THREE.Mesh(new THREE.CylinderGeometry(0.06, 0.06, 3.5, 8), streetPoleMat);
                arm.rotation.z = Math.PI / 2;
                arm.position.set(ix.x + 3.4, 4.6, ix.z + 5.2);
                streetGroup.add(arm);

                const signalBox = new THREE.Mesh(new THREE.BoxGeometry(0.4, 1.2, 0.4), streetPoleMat);
                signalBox.position.set(ix.x + 2.2, 4.4, ix.z + 5.2);
                streetGroup.add(signalBox);

                const lightGreen = new THREE.Mesh(new THREE.SphereGeometry(0.12, 8, 8), new THREE.MeshBasicMaterial({ color: 0x10b981 }));
                lightGreen.position.set(ix.x + 2.2, 4.15, ix.z + 5.42);
                streetGroup.add(lightGreen);

                const lightRed = new THREE.Mesh(new THREE.SphereGeometry(0.12, 8, 8), new THREE.MeshBasicMaterial({ color: 0xef4444 }));
                lightRed.position.set(ix.x + 2.2, 4.65, ix.z + 5.42);
                streetGroup.add(lightRed);
            });

            // 3. Street Furniture: Modern LED Streetlamps & Landscaping Trees along Sidewalks
            const lampPositions = [
                { x: -110, z: -51 }, { x: -80, z: -51 }, { x: -20, z: -51 }, { x: 15, z: -51 }, { x: 75, z: -51 }, { x: 110, z: -51 },
                { x: -110, z: -39 }, { x: -80, z: -39 }, { x: -20, z: -39 }, { x: 15, z: -39 }, { x: 75, z: -39 }, { x: 110, z: -39 },
                { x: -110, z: 39 },  { x: -80, z: 39 },  { x: -20, z: 39 },  { x: 15, z: 39 },  { x: 75, z: 39 },  { x: 110, z: 39 },
                { x: -110, z: 51 },  { x: -80, z: 51 },  { x: -20, z: 51 },  { x: 15, z: 51 },  { x: 75, z: 51 },  { x: 110, z: 51 },
                { x: -54, z: -110 }, { x: -54, z: -80 }, { x: -54, z: -15 }, { x: -54, z: 15 }, { x: -54, z: 80 }, { x: -54, z: 110 },
                { x: 54, z: -110 },  { x: 54, z: -80 },  { x: 54, z: -15 },  { x: 54, z: 15 },  { x: 54, z: 80 },  { x: 54, z: 110 }
            ];

            lampPositions.forEach(pos => {
                const lampGroup = new THREE.Group();
                lampGroup.position.set(pos.x, 0.1, pos.z);

                const pole = new THREE.Mesh(new THREE.CylinderGeometry(0.06, 0.09, 4.2, 8), streetPoleMat);
                pole.position.y = 2.1;
                lampGroup.add(pole);

                const head = new THREE.Mesh(new THREE.BoxGeometry(0.35, 0.1, 0.8), streetPoleMat);
                head.position.set(0, 4.2, 0.25);
                lampGroup.add(head);

                const bulb = new THREE.Mesh(new THREE.BoxGeometry(0.28, 0.04, 0.6), streetBulbMat);
                bulb.position.set(0, 4.13, 0.25);
                lampGroup.add(bulb);

                streetGroup.add(lampGroup);
            });

            // Street Trees (Alternating along sidewalks)
            const treePositions = [
                { x: -95, z: -51 }, { x: -35, z: -51 }, { x: 30, z: -51 }, { x: 95, z: -51 },
                { x: -95, z: 51 },  { x: -35, z: 51 },  { x: 30, z: 51 },  { x: 95, z: 51 },
                { x: -54, z: -95 }, { x: -54, z: -30 }, { x: -54, z: 35 }, { x: -54, z: 95 },
                { x: 54, z: -95 },  { x: 54, z: -30 },  { x: 54, z: 35 },  { x: 54, z: 95 }
            ];

            treePositions.forEach(tp => {
                const treeGroup = new THREE.Group();
                treeGroup.position.set(tp.x, 0.1, tp.z);

                const grate = new THREE.Mesh(
                    new THREE.BoxGeometry(1.2, 0.02, 1.2),
                    new THREE.MeshStandardMaterial({ color: 0x334155, metalness: 0.9, roughness: 0.4 })
                );
                grate.position.y = 0.08;
                treeGroup.add(grate);

                const trunk = new THREE.Mesh(new THREE.CylinderGeometry(0.12, 0.16, 2.6, 8), treeTrunkMat);
                trunk.position.y = 1.38;
                treeGroup.add(trunk);

                const foliage = new THREE.Mesh(new THREE.DodecahedronGeometry(1.25, 1), treeLeavesMat);
                foliage.position.y = 3.2;
                treeGroup.add(foliage);

                const foliageTop = new THREE.Mesh(new THREE.DodecahedronGeometry(0.85, 1), treeLeavesMat);
                foliageTop.position.set(0.1, 4.0, -0.1);
                treeGroup.add(foliageTop);

                streetGroup.add(treeGroup);
            });

            // Modern Glass Transit Bus Shelters
            const busShelterPositions = [
                { x: -28, z: -50.8, rotY: 0 },
                { x: 32, z: 50.8, rotY: Math.PI },
                { x: -53.8, z: 25, rotY: Math.PI / 2 },
                { x: 53.8, z: -25, rotY: -Math.PI / 2 }
            ];

            busShelterPositions.forEach(bsp => {
                const shelter = new THREE.Group();
                shelter.position.set(bsp.x, 0.1, bsp.z);
                shelter.rotation.y = bsp.rotY;

                const frameMat = new THREE.MeshStandardMaterial({ color: 0x1e293b, metalness: 0.9, roughness: 0.2 });
                const roof = new THREE.Mesh(new THREE.BoxGeometry(4.2, 0.1, 1.8), frameMat);
                roof.position.set(0, 2.6, 0);
                shelter.add(roof);

                const backGlass = new THREE.Mesh(new THREE.BoxGeometry(4.0, 2.4, 0.06), busGlassMat);
                backGlass.position.set(0, 1.3, -0.85);
                shelter.add(backGlass);

                const sideGlass1 = new THREE.Mesh(new THREE.BoxGeometry(0.06, 2.4, 1.6), busGlassMat);
                sideGlass1.position.set(-2.0, 1.3, 0);
                shelter.add(sideGlass1);

                const sideGlass2 = new THREE.Mesh(new THREE.BoxGeometry(0.06, 2.4, 1.6), busGlassMat);
                sideGlass2.position.set(2.0, 1.3, 0);
                shelter.add(sideGlass2);

                const bench = new THREE.Mesh(
                    new THREE.BoxGeometry(2.6, 0.08, 0.45),
                    new THREE.MeshStandardMaterial({ color: 0x92400e, roughness: 0.7 })
                );
                bench.position.set(0, 0.55, -0.5);
                shelter.add(bench);

                const sign = new THREE.Mesh(
                    new THREE.BoxGeometry(0.8, 1.2, 0.04),
                    new THREE.MeshBasicMaterial({ color: 0x38bdf8 })
                );
                sign.position.set(1.9, 1.3, 0);
                shelter.add(sign);

                streetGroup.add(shelter);
            });

            // 4. Dynamic Moving Vehicles Ecosystem (24 Active Vehicles)
            const vehicleConfigs = [
                // North Ave Eastbound (lane z = -43.0, moving +x, dirSign = 1)
                { type: 'sedan', color: 0x0f172a, x: -120, z: -43.0, dirAxis: 'x', dirSign: 1, speed: 13.5 },
                { type: 'taxi',  color: 0xf59e0b, x: -70,  z: -43.0, dirAxis: 'x', dirSign: 1, speed: 15.0 },
                { type: 'bus',   color: 0x1d4ed8, x: -10,  z: -43.0, dirAxis: 'x', dirSign: 1, speed: 9.5 },
                { type: 'suv',   color: 0x475569, x: 50,   z: -43.0, dirAxis: 'x', dirSign: 1, speed: 14.0 },
                { type: 'sedan', color: 0xf8fafc, x: 105,  z: -43.0, dirAxis: 'x', dirSign: 1, speed: 12.5 },

                // North Ave Westbound (lane z = -47.0, moving -x, dirSign = -1)
                { type: 'van',   color: 0x3b82f6, x: 125,  z: -47.0, dirAxis: 'x', dirSign: -1, speed: 11.5 },
                { type: 'sedan', color: 0xb91c1c, x: 75,   z: -47.0, dirAxis: 'x', dirSign: -1, speed: 14.5 },
                { type: 'suv',   color: 0x111827, x: 20,   z: -47.0, dirAxis: 'x', dirSign: -1, speed: 13.0 },
                { type: 'taxi',  color: 0xf59e0b, x: -45,  z: -47.0, dirAxis: 'x', dirSign: -1, speed: 16.0 },
                { type: 'sedan', color: 0x64748b, x: -105, z: -47.0, dirAxis: 'x', dirSign: -1, speed: 12.0 },

                // South Ave Eastbound (lane z = 47.0, moving +x, dirSign = 1)
                { type: 'bus',   color: 0x047857, x: -115, z: 47.0, dirAxis: 'x', dirSign: 1, speed: 9.0 },
                { type: 'suv',   color: 0xf8fafc, x: -60,  z: 47.0, dirAxis: 'x', dirSign: 1, speed: 13.5 },
                { type: 'sedan', color: 0x0284c7, x: 5,    z: 47.0, dirAxis: 'x', dirSign: 1, speed: 14.0 },
                { type: 'taxi',  color: 0xf59e0b, x: 65,   z: 47.0, dirAxis: 'x', dirSign: 1, speed: 15.5 },

                // South Ave Westbound (lane z = 43.0, moving -x, dirSign = -1)
                { type: 'sedan', color: 0x1f2937, x: 115,  z: 43.0, dirAxis: 'x', dirSign: -1, speed: 13.0 },
                { type: 'van',   color: 0xd97706, x: 55,   z: 43.0, dirAxis: 'x', dirSign: -1, speed: 11.0 },
                { type: 'suv',   color: 0x334155, x: 0,    z: 43.0, dirAxis: 'x', dirSign: -1, speed: 14.0 },
                { type: 'sedan', color: 0x991b1b, x: -65,  z: 43.0, dirAxis: 'x', dirSign: -1, speed: 12.5 },

                // West Ave Southbound (lane x = -46.0, moving +z, dirSign = 1)
                { type: 'sedan', color: 0xf8fafc, x: -46.0, z: -110, dirAxis: 'z', dirSign: 1, speed: 13.0 },
                { type: 'taxi',  color: 0xf59e0b, x: -46.0, z: -35,  dirAxis: 'z', dirSign: 1, speed: 15.0 },
                { type: 'bus',   color: 0x6d28d9, x: -46.0, z: 40,   dirAxis: 'z', dirSign: 1, speed: 9.2 },

                // West Ave Northbound (lane x = -50.0, moving -z, dirSign = -1)
                { type: 'suv',   color: 0x1e3a8a, x: -50.0, z: 115,  dirAxis: 'z', dirSign: -1, speed: 13.8 },
                { type: 'van',   color: 0xf3f4f6, x: -50.0, z: 35,   dirAxis: 'z', dirSign: -1, speed: 11.2 },
                { type: 'sedan', color: 0x374151, x: -50.0, z: -40,  dirAxis: 'z', dirSign: -1, speed: 12.8 },

                // East Ave Southbound (lane x = 50.0, moving +z, dirSign = 1)
                { type: 'van',   color: 0x059669, x: 50.0, z: -105, dirAxis: 'z', dirSign: 1, speed: 11.8 },
                { type: 'sedan', color: 0x111827, x: 50.0, z: -25,  dirAxis: 'z', dirSign: 1, speed: 14.2 },
                { type: 'taxi',  color: 0xf59e0b, x: 50.0, z: 50,   dirAxis: 'z', dirSign: 1, speed: 15.5 },

                // East Ave Northbound (lane x = 46.0, moving -z, dirSign = -1)
                { type: 'bus',   color: 0xbe123c, x: 46.0, z: 110,  dirAxis: 'z', dirSign: -1, speed: 9.0 },
                { type: 'suv',   color: 0x475569, x: 46.0, z: 30,   dirAxis: 'z', dirSign: -1, speed: 13.5 },
                { type: 'sedan', color: 0xf8fafc, x: 46.0, z: -45,  dirAxis: 'z', dirSign: -1, speed: 13.0 }
            ];

            vehicleConfigs.forEach(vc => {
                const mesh = this.createVehicleModel(vc.type, vc.color);
                mesh.position.set(vc.x, 0.1, vc.z);

                if (vc.dirAxis === 'x') {
                    mesh.rotation.y = (vc.dirSign > 0) ? -Math.PI / 2 : Math.PI / 2;
                } else {
                    mesh.rotation.y = (vc.dirSign > 0) ? 0 : Math.PI;
                }

                streetGroup.add(mesh);

                this.cityVehicles.push({
                    mesh: mesh,
                    dirAxis: vc.dirAxis,
                    dirSign: vc.dirSign,
                    speed: vc.speed,
                    minCoord: -135,
                    maxCoord: 135
                });
            });

            // 5. Urban Street Pedestrians & Active NPCs (Sidewalk Walkers, Crossers, Groups)
            const streetNpcConfigs = [
                // North Sidewalk Walkers
                { role: 'walker', x: -65, z: -51.2, dir: 1, axis: 'x', min: -100, max: 100, speed: 1.35, suitColor: 0x1e293b, skinColor: 0xfcd34d },
                { role: 'walker', x: 25,  z: -51.2, dir: -1, axis: 'x', min: -100, max: 100, speed: 1.25, suitColor: 0x334155, skinColor: 0xfbbf24 },
                { role: 'walker', x: -15, z: -38.8, dir: 1, axis: 'x', min: -90, max: 90, speed: 1.4, suitColor: 0x0f172a, skinColor: 0xfde68a },

                // South Sidewalk Walkers
                { role: 'walker', x: -45, z: 38.8, dir: 1, axis: 'x', min: -100, max: 100, speed: 1.3, suitColor: 0x1e3a8a, skinColor: 0xfca5a5 },
                { role: 'walker', x: 40,  z: 51.2, dir: -1, axis: 'x', min: -100, max: 100, speed: 1.2, suitColor: 0x475569, skinColor: 0xfcd34d },

                // West Sidewalk Walkers
                { role: 'walker', x: -53.8, z: -60, dir: 1, axis: 'z', min: -100, max: 100, speed: 1.3, suitColor: 0x312e81, skinColor: 0xfbbf24 },
                { role: 'walker', x: -42.2, z: 30,  dir: -1, axis: 'z', min: -100, max: 100, speed: 1.35, suitColor: 0x111827, skinColor: 0xfcd34d },

                // East Sidewalk Walkers
                { role: 'walker', x: 53.8, z: -40, dir: 1, axis: 'z', min: -100, max: 100, speed: 1.25, suitColor: 0x1e293b, skinColor: 0xfde68a },
                { role: 'walker', x: 42.2, z: 50,  dir: -1, axis: 'z', min: -100, max: 100, speed: 1.3, suitColor: 0x064e3b, skinColor: 0xfca5a5 },

                // Zebra Crosswalk Crossing NPCs
                { role: 'crosser', x: -48, z: -38.5, dir: -1, axis: 'z', min: -51.5, max: -38.5, speed: 1.1, suitColor: 0x7c2d12, skinColor: 0xfcd34d },
                { role: 'crosser', x: 48,  z: 38.5,  dir: 1, axis: 'z', min: 38.5, max: 51.5, speed: 1.15, suitColor: 0x1f2937, skinColor: 0xfbbf24 },

                // Bus Shelter Waiting NPCs
                { role: 'waiting', x: -28.5, z: -50.2, rotY: 0, suitColor: 0x3b82f6, skinColor: 0xfcd34d },
                { role: 'waiting', x: 31.5,  z: 50.2,  rotY: Math.PI, suitColor: 0x475569, skinColor: 0xfde68a },

                // Small Conversational Group on Plaza Corner
                { role: 'conversing', x: -41.5, z: -37.5, rotY: Math.PI / 4, suitColor: 0x1e293b, skinColor: 0xfcd34d, partnerId: 1 },
                { role: 'conversing', x: -40.6, z: -36.6, rotY: -3 * Math.PI / 4, suitColor: 0x475569, skinColor: 0xfbbf24, partnerId: 0 }
            ];

            streetNpcConfigs.forEach((cfg, idx) => {
                const npc = this.createCityNPC({
                    suitColor: cfg.suitColor,
                    skinColor: cfg.skinColor,
                    hairColor: 0x1c1917,
                    pantsColor: 0x1e293b
                });
                npc.position.set(cfg.x, 0.18, cfg.z);
                if (cfg.rotY !== undefined) npc.rotation.y = cfg.rotY;

                streetGroup.add(npc);

                this.cityNPCs.push({
                    mesh: npc,
                    role: cfg.role,
                    axis: cfg.axis,
                    dir: cfg.dir,
                    min: cfg.min,
                    max: cfg.max,
                    speed: cfg.speed || 1.25,
                    leftLeg: npc.userData.leftLeg,
                    rightLeg: npc.userData.rightLeg,
                    leftArm: npc.userData.leftArm,
                    rightArm: npc.userData.rightArm,
                    head: npc.userData.head,
                    seed: idx * 1.7,
                    isTerrace: false
                });
            });

            parent.add(streetGroup);
        }

        createVehicleModel(type, colorHex) {
            const vGroup = new THREE.Group();
            vGroup.name = `vehicle_${type}`;

            const bodyMat = new THREE.MeshStandardMaterial({
                color: colorHex,
                metalness: 0.8,
                roughness: 0.3
            });
            const glassMat = new THREE.MeshStandardMaterial({
                color: 0x0f172a,
                metalness: 0.9,
                roughness: 0.1
            });
            const tireMat = new THREE.MeshStandardMaterial({
                color: 0x18181b,
                roughness: 0.95
            });
            const rimMat = new THREE.MeshStandardMaterial({
                color: 0xe2e8f0,
                metalness: 0.9,
                roughness: 0.2
            });
            const headlightMat = new THREE.MeshBasicMaterial({ color: 0xffffff });
            const taillightMat = new THREE.MeshBasicMaterial({ color: 0xef4444 });

            const addWheel = (wx, wy, wz, r = 0.36, w = 0.22) => {
                const wheel = new THREE.Mesh(new THREE.CylinderGeometry(r, r, w, 12), tireMat);
                wheel.rotation.z = Math.PI / 2;
                wheel.position.set(wx, wy, wz);

                const rim = new THREE.Mesh(new THREE.CylinderGeometry(r * 0.58, r * 0.58, w + 0.02, 8), rimMat);
                rim.rotation.z = Math.PI / 2;
                wheel.add(rim);

                vGroup.add(wheel);
            };

            if (type === 'bus') {
                // High-Capacity City Transit Bus (L: 10.5m, W: 2.5m, H: 3.2m)
                const busBody = new THREE.Mesh(new THREE.BoxGeometry(2.4, 2.6, 10.2), bodyMat);
                busBody.position.y = 1.75;
                vGroup.add(busBody);

                // Passenger Panoramic Side Windows
                const winBand = new THREE.Mesh(new THREE.BoxGeometry(2.44, 1.1, 8.8), glassMat);
                winBand.position.set(0, 2.05, 0.2);
                vGroup.add(winBand);

                // Front Windshield
                const windshield = new THREE.Mesh(new THREE.BoxGeometry(2.35, 1.4, 0.2), glassMat);
                windshield.position.set(0, 1.9, 5.1);
                vGroup.add(windshield);

                // Route Destination LED Signboard
                const signMat = new THREE.MeshBasicMaterial({ color: 0xf59e0b });
                const routeSign = new THREE.Mesh(new THREE.BoxGeometry(1.6, 0.35, 0.05), signMat);
                routeSign.position.set(0, 2.8, 5.12);
                vGroup.add(routeSign);

                // Roof AC / Ventilation Pods
                const acUnit1 = new THREE.Mesh(new THREE.BoxGeometry(1.6, 0.3, 2.2), rimMat);
                acUnit1.position.set(0, 3.2, 1.0);
                vGroup.add(acUnit1);

                const acUnit2 = new THREE.Mesh(new THREE.BoxGeometry(1.6, 0.3, 2.2), rimMat);
                acUnit2.position.set(0, 3.2, -2.5);
                vGroup.add(acUnit2);

                // 6 Wheels
                addWheel(-1.18, 0.46, 3.5, 0.46, 0.26);
                addWheel(1.18, 0.46, 3.5, 0.46, 0.26);
                addWheel(-1.18, 0.46, -2.4, 0.46, 0.26);
                addWheel(1.18, 0.46, -2.4, 0.46, 0.26);
                addWheel(-1.18, 0.46, -3.7, 0.46, 0.26);
                addWheel(1.18, 0.46, -3.7, 0.46, 0.26);

                // Headlights & Taillights
                const hlL = new THREE.Mesh(new THREE.BoxGeometry(0.35, 0.22, 0.08), headlightMat);
                hlL.position.set(-0.85, 0.85, 5.12);
                vGroup.add(hlL);
                const hlR = new THREE.Mesh(new THREE.BoxGeometry(0.35, 0.22, 0.08), headlightMat);
                hlR.position.set(0.85, 0.85, 5.12);
                vGroup.add(hlR);

                const tlL = new THREE.Mesh(new THREE.BoxGeometry(0.35, 0.45, 0.08), taillightMat);
                tlL.position.set(-0.85, 1.1, -5.12);
                vGroup.add(tlL);
                const tlR = new THREE.Mesh(new THREE.BoxGeometry(0.35, 0.45, 0.08), taillightMat);
                tlR.position.set(0.85, 1.1, -5.12);
                vGroup.add(tlR);

            } else if (type === 'van') {
                // Commercial Delivery / Courier Van (L: 5.4m, W: 2.05m, H: 2.3m)
                const vanBody = new THREE.Mesh(new THREE.BoxGeometry(2.0, 1.7, 5.2), bodyMat);
                vanBody.position.y = 1.25;
                vGroup.add(vanBody);

                const hood = new THREE.Mesh(new THREE.BoxGeometry(1.95, 0.75, 1.2), bodyMat);
                hood.position.set(0, 0.85, 2.3);
                vGroup.add(hood);

                const windshield = new THREE.Mesh(new THREE.BoxGeometry(1.85, 0.75, 0.1), glassMat);
                windshield.position.set(0, 1.5, 1.72);
                windshield.rotation.x = -0.3;
                vGroup.add(windshield);

                addWheel(-0.98, 0.38, 1.7, 0.38, 0.24);
                addWheel(0.98, 0.38, 1.7, 0.38, 0.24);
                addWheel(-0.98, 0.38, -1.7, 0.38, 0.24);
                addWheel(0.98, 0.38, -1.7, 0.38, 0.24);

                const hlL = new THREE.Mesh(new THREE.BoxGeometry(0.3, 0.18, 0.06), headlightMat);
                hlL.position.set(-0.7, 0.75, 2.92);
                vGroup.add(hlL);
                const hlR = new THREE.Mesh(new THREE.BoxGeometry(0.3, 0.18, 0.06), headlightMat);
                hlR.position.set(0.7, 0.75, 2.92);
                vGroup.add(hlR);

                const tlL = new THREE.Mesh(new THREE.BoxGeometry(0.2, 0.6, 0.06), taillightMat);
                tlL.position.set(-0.8, 1.2, -2.62);
                vGroup.add(tlL);
                const tlR = new THREE.Mesh(new THREE.BoxGeometry(0.2, 0.6, 0.06), taillightMat);
                tlR.position.set(0.8, 1.2, -2.62);
                vGroup.add(tlR);

            } else if (type === 'suv') {
                // Modern Full-Size SUV (L: 4.8m, W: 1.95m, H: 1.75m)
                const lowerBody = new THREE.Mesh(new THREE.BoxGeometry(1.9, 0.7, 4.6), bodyMat);
                lowerBody.position.y = 0.68;
                vGroup.add(lowerBody);

                const cabin = new THREE.Mesh(new THREE.BoxGeometry(1.7, 0.65, 2.8), glassMat);
                cabin.position.set(0, 1.3, -0.2);
                vGroup.add(cabin);

                const roof = new THREE.Mesh(new THREE.BoxGeometry(1.68, 0.08, 2.65), bodyMat);
                roof.position.set(0, 1.65, -0.2);
                vGroup.add(roof);

                const railMat = new THREE.MeshStandardMaterial({ color: 0x334155, metalness: 0.9 });
                const railL = new THREE.Mesh(new THREE.BoxGeometry(0.06, 0.08, 2.4), railMat);
                railL.position.set(-0.72, 1.72, -0.2);
                vGroup.add(railL);
                const railR = new THREE.Mesh(new THREE.BoxGeometry(0.06, 0.08, 2.4), railMat);
                railR.position.set(0.72, 1.72, -0.2);
                vGroup.add(railR);

                addWheel(-0.95, 0.4, 1.45, 0.4, 0.25);
                addWheel(0.95, 0.4, 1.45, 0.4, 0.25);
                addWheel(-0.95, 0.4, -1.45, 0.4, 0.25);
                addWheel(0.95, 0.4, -1.45, 0.4, 0.25);

                const hlL = new THREE.Mesh(new THREE.BoxGeometry(0.32, 0.16, 0.06), headlightMat);
                hlL.position.set(-0.68, 0.75, 2.32);
                vGroup.add(hlL);
                const hlR = new THREE.Mesh(new THREE.BoxGeometry(0.32, 0.16, 0.06), headlightMat);
                hlR.position.set(0.68, 0.75, 2.32);
                vGroup.add(hlR);

                const tlL = new THREE.Mesh(new THREE.BoxGeometry(0.32, 0.16, 0.06), taillightMat);
                tlL.position.set(-0.68, 0.85, -2.32);
                vGroup.add(tlL);
                const tlR = new THREE.Mesh(new THREE.BoxGeometry(0.32, 0.16, 0.06), taillightMat);
                tlR.position.set(0.68, 0.85, -2.32);
                vGroup.add(tlR);

            } else {
                // Sedan & Taxi (L: 4.4m, W: 1.85m, H: 1.45m)
                const lowerBody = new THREE.Mesh(new THREE.BoxGeometry(1.82, 0.58, 4.3), bodyMat);
                lowerBody.position.y = 0.55;
                vGroup.add(lowerBody);

                const cabin = new THREE.Mesh(new THREE.BoxGeometry(1.55, 0.52, 2.2), glassMat);
                cabin.position.set(0, 1.05, -0.15);
                vGroup.add(cabin);

                const roof = new THREE.Mesh(new THREE.BoxGeometry(1.52, 0.06, 1.95), bodyMat);
                roof.position.set(0, 1.33, -0.15);
                vGroup.add(roof);

                if (type === 'taxi') {
                    const taxiSignMat = new THREE.MeshBasicMaterial({ color: 0xfef08a });
                    const taxiSign = new THREE.Mesh(new THREE.BoxGeometry(0.65, 0.18, 0.22), taxiSignMat);
                    taxiSign.position.set(0, 1.46, -0.15);
                    vGroup.add(taxiSign);
                }

                addWheel(-0.9, 0.34, 1.3, 0.34, 0.22);
                addWheel(0.9, 0.34, 1.3, 0.34, 0.22);
                addWheel(-0.9, 0.34, -1.3, 0.34, 0.22);
                addWheel(0.9, 0.34, -1.3, 0.34, 0.22);

                const hlL = new THREE.Mesh(new THREE.BoxGeometry(0.28, 0.14, 0.06), headlightMat);
                hlL.position.set(-0.64, 0.6, 2.16);
                vGroup.add(hlL);
                const hlR = new THREE.Mesh(new THREE.BoxGeometry(0.28, 0.14, 0.06), headlightMat);
                hlR.position.set(0.64, 0.6, 2.16);
                vGroup.add(hlR);

                const tlL = new THREE.Mesh(new THREE.BoxGeometry(0.28, 0.14, 0.06), taillightMat);
                tlL.position.set(-0.64, 0.65, -2.16);
                vGroup.add(tlL);
                const tlR = new THREE.Mesh(new THREE.BoxGeometry(0.28, 0.14, 0.06), taillightMat);
                tlR.position.set(0.64, 0.65, -2.16);
                vGroup.add(tlR);
            }

            return vGroup;
        }

        createCityNPC(config = {}) {
            const npcGroup = new THREE.Group();
            npcGroup.name = 'city_npc';

            const suitMat = new THREE.MeshStandardMaterial({
                color: config.suitColor || 0x1e293b,
                roughness: 0.7
            });
            const pantsMat = new THREE.MeshStandardMaterial({
                color: config.pantsColor || 0x111827,
                roughness: 0.8
            });
            const skinMat = new THREE.MeshStandardMaterial({
                color: config.skinColor || 0xfcd34d,
                roughness: 0.6
            });
            const hairMat = new THREE.MeshStandardMaterial({
                color: config.hairColor || 0x1c1917,
                roughness: 0.8
            });
            const shoeMat = new THREE.MeshStandardMaterial({
                color: 0x09090b,
                roughness: 0.4
            });

            // 1. Torso / Suit Jacket
            const torso = new THREE.Mesh(new THREE.BoxGeometry(0.42, 0.52, 0.24), suitMat);
            torso.position.y = 1.18;
            npcGroup.add(torso);

            // 2. Head with Hair
            const headGroup = new THREE.Group();
            headGroup.position.set(0, 1.58, 0);

            const head = new THREE.Mesh(new THREE.BoxGeometry(0.22, 0.24, 0.22), skinMat);
            headGroup.add(head);

            const hair = new THREE.Mesh(new THREE.BoxGeometry(0.24, 0.1, 0.24), hairMat);
            hair.position.y = 0.1;
            headGroup.add(hair);

            npcGroup.add(headGroup);

            // 3. Articulated Legs
            const leftLegGroup = new THREE.Group();
            leftLegGroup.position.set(-0.12, 0.92, 0);
            const leftLeg = new THREE.Mesh(new THREE.BoxGeometry(0.15, 0.86, 0.16), pantsMat);
            leftLeg.position.y = -0.43;
            leftLegGroup.add(leftLeg);
            const leftShoe = new THREE.Mesh(new THREE.BoxGeometry(0.15, 0.08, 0.22), shoeMat);
            leftShoe.position.set(0, -0.86, 0.03);
            leftLegGroup.add(leftShoe);
            npcGroup.add(leftLegGroup);

            const rightLegGroup = new THREE.Group();
            rightLegGroup.position.set(0.12, 0.92, 0);
            const rightLeg = new THREE.Mesh(new THREE.BoxGeometry(0.15, 0.86, 0.16), pantsMat);
            rightLeg.position.y = -0.43;
            rightLegGroup.add(rightLeg);
            const rightShoe = new THREE.Mesh(new THREE.BoxGeometry(0.15, 0.08, 0.22), shoeMat);
            rightShoe.position.set(0, -0.86, 0.03);
            rightLegGroup.add(rightShoe);
            npcGroup.add(rightLegGroup);

            // 4. Articulated Arms
            const leftArmGroup = new THREE.Group();
            leftArmGroup.position.set(-0.27, 1.40, 0);
            const leftArm = new THREE.Mesh(new THREE.BoxGeometry(0.12, 0.58, 0.14), suitMat);
            leftArm.position.y = -0.29;
            leftArmGroup.add(leftArm);
            const leftHand = new THREE.Mesh(new THREE.BoxGeometry(0.09, 0.12, 0.09), skinMat);
            leftHand.position.y = -0.62;
            leftArmGroup.add(leftHand);
            npcGroup.add(leftArmGroup);

            const rightArmGroup = new THREE.Group();
            rightArmGroup.position.set(0.27, 1.40, 0);
            const rightArm = new THREE.Mesh(new THREE.BoxGeometry(0.12, 0.58, 0.14), suitMat);
            rightArm.position.y = -0.29;
            rightArmGroup.add(rightArm);
            const rightHand = new THREE.Mesh(new THREE.BoxGeometry(0.09, 0.12, 0.09), skinMat);
            rightHand.position.y = -0.62;
            rightArmGroup.add(rightHand);
            npcGroup.add(rightArmGroup);

            npcGroup.userData = {
                head: headGroup,
                leftLeg: leftLegGroup,
                rightLeg: rightLegGroup,
                leftArm: leftArmGroup,
                rightArm: rightArmGroup
            };

            return npcGroup;
        }

        buildSkyHelicopter(parent) {
            const heliGroup = new THREE.Group();
            heliGroup.name = 'sky_helicopter';
            heliGroup.position.set(85, 23.5, 60);

            const bodyMat = new THREE.MeshStandardMaterial({
                color: 0x0f172a, // Executive VIP Navy Obsidian
                metalness: 0.85,
                roughness: 0.25
            });
            const stripeMat = new THREE.MeshStandardMaterial({
                color: 0xf1f5f9, // Titanium Silver Livery Stripe
                metalness: 0.9,
                roughness: 0.2
            });
            const glassMat = new THREE.MeshStandardMaterial({
                color: 0x38bdf8,
                roughness: 0.1,
                metalness: 0.9,
                transparent: true,
                opacity: 0.65
            });
            const metalMat = new THREE.MeshStandardMaterial({
                color: 0x475569,
                metalness: 0.95,
                roughness: 0.2
            });
            const bladeMat = new THREE.MeshStandardMaterial({
                color: 0x18181b,
                metalness: 0.7,
                roughness: 0.3
            });

            // 1. Aerodynamic Main Cabin Fuselage
            const cabinGeom = new THREE.BoxGeometry(1.8, 1.8, 4.4);
            const cabin = new THREE.Mesh(cabinGeom, bodyMat);
            cabin.position.y = 1.3;
            heliGroup.add(cabin);

            // Front Cockpit Bubble
            const noseGeom = new THREE.ConeGeometry(1.1, 1.6, 8);
            const nose = new THREE.Mesh(noseGeom, bodyMat);
            nose.rotation.x = Math.PI / 2;
            nose.position.set(0, 1.25, 2.7);
            heliGroup.add(nose);

            // Tinted Canopy Windshield
            const canopyGeom = new THREE.BoxGeometry(1.72, 1.1, 1.8);
            const canopy = new THREE.Mesh(canopyGeom, glassMat);
            canopy.position.set(0, 1.65, 1.2);
            heliGroup.add(canopy);

            // Livery Side Stripe
            const stripeL = new THREE.Mesh(new THREE.BoxGeometry(0.04, 0.18, 4.2), stripeMat);
            stripeL.position.set(-0.92, 1.2, 0);
            heliGroup.add(stripeL);
            const stripeR = new THREE.Mesh(new THREE.BoxGeometry(0.04, 0.18, 4.2), stripeMat);
            stripeR.position.set(0.92, 1.2, 0);
            heliGroup.add(stripeR);

            // 2. Twin Turbine Engine Cowling
            const engine = new THREE.Mesh(new THREE.BoxGeometry(1.4, 0.7, 2.2), metalMat);
            engine.position.set(0, 2.45, -0.3);
            heliGroup.add(engine);

            // 3. Tail Boom & Empennage
            const tailBoom = new THREE.Mesh(new THREE.CylinderGeometry(0.22, 0.42, 5.6, 8), bodyMat);
            tailBoom.rotation.x = Math.PI / 2;
            tailBoom.position.set(0, 1.6, -4.6);
            heliGroup.add(tailBoom);

            // Vertical Stabilizer Fin
            const fin = new THREE.Mesh(new THREE.BoxGeometry(0.12, 1.6, 0.85), bodyMat);
            fin.position.set(0, 2.4, -7.2);
            heliGroup.add(fin);

            // Horizontal Stabilizer Wing
            const hWing = new THREE.Mesh(new THREE.BoxGeometry(1.8, 0.08, 0.45), bodyMat);
            hWing.position.set(0, 1.8, -6.8);
            heliGroup.add(hWing);

            // 4. Landing Skids
            const addSkid = (sideX) => {
                const skidTube = new THREE.Mesh(new THREE.CylinderGeometry(0.06, 0.06, 4.8, 8), metalMat);
                skidTube.rotation.x = Math.PI / 2;
                skidTube.position.set(sideX, 0.15, 0);
                heliGroup.add(skidTube);

                const strutF = new THREE.Mesh(new THREE.CylinderGeometry(0.05, 0.05, 1.25, 8), metalMat);
                strutF.rotation.z = sideX > 0 ? -0.4 : 0.4;
                strutF.position.set(sideX * 0.7, 0.65, 1.2);
                heliGroup.add(strutF);

                const strutR = new THREE.Mesh(new THREE.CylinderGeometry(0.05, 0.05, 1.25, 8), metalMat);
                strutR.rotation.z = sideX > 0 ? -0.4 : 0.4;
                strutR.position.set(sideX * 0.7, 0.65, -1.2);
                heliGroup.add(strutR);
            };
            addSkid(-1.0);
            addSkid(1.0);

            // 5. Main Rotor Assembly
            const mastGeom = new THREE.CylinderGeometry(0.08, 0.08, 0.65, 8);
            const mast = new THREE.Mesh(mastGeom, metalMat);
            mast.position.set(0, 2.9, -0.3);
            heliGroup.add(mast);

            this.mainRotorMesh = new THREE.Group();
            this.mainRotorMesh.position.set(0, 3.22, -0.3);

            const hub = new THREE.Mesh(new THREE.CylinderGeometry(0.35, 0.35, 0.14, 12), metalMat);
            this.mainRotorMesh.add(hub);

            for (let b = 0; b < 4; b++) {
                const blade = new THREE.Mesh(new THREE.BoxGeometry(0.24, 0.03, 4.8), bladeMat);
                blade.rotation.y = b * (Math.PI / 2);
                blade.position.set(
                    Math.sin(b * Math.PI / 2) * 2.5,
                    0,
                    Math.cos(b * Math.PI / 2) * 2.5
                );
                this.mainRotorMesh.add(blade);
            }
            heliGroup.add(this.mainRotorMesh);

            // 6. Tail Rotor Assembly
            this.tailRotorMesh = new THREE.Group();
            this.tailRotorMesh.position.set(0.16, 2.65, -7.2);

            const tailHub = new THREE.Mesh(new THREE.CylinderGeometry(0.12, 0.12, 0.08, 8), metalMat);
            tailHub.rotation.z = Math.PI / 2;
            this.tailRotorMesh.add(tailHub);

            const tailBlade1 = new THREE.Mesh(new THREE.BoxGeometry(0.02, 1.4, 0.12), bladeMat);
            this.tailRotorMesh.add(tailBlade1);
            heliGroup.add(this.tailRotorMesh);

            // 7. Navigation Lights & Flashing Red Anti-Collision Strobe Beacon
            const portNav = new THREE.Mesh(new THREE.SphereGeometry(0.08, 8, 8), new THREE.MeshBasicMaterial({ color: 0xef4444 }));
            portNav.position.set(-1.0, 1.6, 0);
            heliGroup.add(portNav);

            const stbdNav = new THREE.Mesh(new THREE.SphereGeometry(0.08, 8, 8), new THREE.MeshBasicMaterial({ color: 0x10b981 }));
            stbdNav.position.set(1.0, 1.6, 0);
            heliGroup.add(stbdNav);

            this.heliStrobeLight = new THREE.Mesh(new THREE.SphereGeometry(0.12, 8, 8), new THREE.MeshBasicMaterial({ color: 0xff0000 }));
            this.heliStrobeLight.position.set(0, 3.25, -7.2);
            heliGroup.add(this.heliStrobeLight);

            this.skyHelicopter = heliGroup;
            parent.add(heliGroup);
        }

        buildSkyTerraceLife(parent) {
            const lifeGroup = new THREE.Group();
            lifeGroup.name = 'sky_terrace_life';
            lifeGroup.position.set(0, 0, 0);

            if (!this.cityNPCs) this.cityNPCs = [];

            // Sky Terrace Occupants (Level 20 Penthouse Deck: Y = 0, Z = 11 to 24.5, X = -25 to 25)
            const terraceConfigs = [
                // 1 & 2: Sightseers at the panoramic glass balustrade
                {
                    role: 'terrace_sightseer',
                    x: -3.5, z: 23.8, rotY: 0,
                    suitColor: 0x1e293b, skinColor: 0xfcd34d, hairColor: 0x18181b,
                    action: 'lean_balustrade'
                },
                {
                    role: 'terrace_sightseer',
                    x: 4.2, z: 23.8, rotY: 0.15,
                    suitColor: 0x475569, skinColor: 0xfde68a, hairColor: 0x78350f,
                    action: 'admire_view'
                },

                // 3 & 4: Executive conversation near lush terrace planter box
                {
                    role: 'terrace_conversing',
                    x: -6.8, z: 16.5, rotY: 2.3,
                    suitColor: 0x0f172a, skinColor: 0xfbbf24, hairColor: 0x111827,
                    action: 'talk_coffee'
                },
                {
                    role: 'terrace_conversing',
                    x: -5.8, z: 17.4, rotY: -0.8,
                    suitColor: 0x1e3a8a, skinColor: 0xfca5a5, hairColor: 0x27272a,
                    action: 'listen_nod'
                },

                // 5 & 6: Strollers walking between sliding glass doors and helipad
                {
                    role: 'terrace_walker',
                    x: 6.0, z: 14.5, rotY: Math.PI / 2,
                    suitColor: 0x064e3b, skinColor: 0xfcd34d, hairColor: 0x451a03,
                    axis: 'x', dir: 1, min: 2.0, max: 10.5, speed: 0.95
                },
                {
                    role: 'terrace_walker',
                    x: 9.5, z: 15.5, rotY: -Math.PI / 2,
                    suitColor: 0x701a75, skinColor: 0xfde68a, hairColor: 0x18181b,
                    axis: 'x', dir: -1, min: 2.0, max: 10.5, speed: 0.88
                },

                // 7 & 8: Executives relaxing on outdoor terrace lounge sofas
                {
                    role: 'terrace_seated',
                    x: -14.6, z: 17.5, rotY: Math.PI / 2,
                    suitColor: 0x334155, skinColor: 0xfcd34d, hairColor: 0x1f2937
                },
                {
                    role: 'terrace_seated',
                    x: -12.4, z: 17.5, rotY: -Math.PI / 2,
                    suitColor: 0x1e293b, skinColor: 0xfbbf24, hairColor: 0x3f3f46
                }
            ];

            terraceConfigs.forEach((cfg, idx) => {
                const npc = this.createCityNPC({
                    suitColor: cfg.suitColor,
                    skinColor: cfg.skinColor,
                    hairColor: cfg.hairColor,
                    pantsColor: 0x1e293b
                });

                npc.position.set(cfg.x, 0, cfg.z);
                if (cfg.rotY !== undefined) npc.rotation.y = cfg.rotY;

                if (cfg.action === 'lean_balustrade') {
                    if (npc.userData.leftArm) npc.userData.leftArm.rotation.set(-0.8, 0, -0.2);
                    if (npc.userData.rightArm) npc.userData.rightArm.rotation.set(-0.8, 0, 0.2);
                    if (npc.userData.head) npc.userData.head.rotation.set(-0.1, 0, 0);
                } else if (cfg.action === 'admire_view') {
                    if (npc.userData.rightArm) npc.userData.rightArm.rotation.set(-0.6, 0.2, 0.3);
                } else if (cfg.action === 'talk_coffee') {
                    if (npc.userData.rightArm) npc.userData.rightArm.rotation.set(-1.1, -0.3, 0.4);
                } else if (cfg.role === 'terrace_seated') {
                    npc.position.y = -0.38;
                    if (npc.userData.leftLeg) npc.userData.leftLeg.rotation.set(-Math.PI / 2, 0, 0);
                    if (npc.userData.rightLeg) npc.userData.rightLeg.rotation.set(-Math.PI / 2, 0, 0);
                }

                lifeGroup.add(npc);

                this.cityNPCs.push({
                    mesh: npc,
                    role: cfg.role,
                    action: cfg.action,
                    axis: cfg.axis,
                    dir: cfg.dir,
                    min: cfg.min,
                    max: cfg.max,
                    speed: cfg.speed || 0.9,
                    leftLeg: npc.userData.leftLeg,
                    rightLeg: npc.userData.rightLeg,
                    leftArm: npc.userData.leftArm,
                    rightArm: npc.userData.rightArm,
                    head: npc.userData.head,
                    seed: idx * 2.3,
                    isTerrace: true
                });
            });

            parent.add(lifeGroup);
        }

        createAntennaSpire(parent, x, yBottom, z, height) {
            const mastMat = new THREE.MeshStandardMaterial({ color: 0x475569, metalness: 0.9, roughness: 0.3 });
            const spire = new THREE.Mesh(new THREE.CylinderGeometry(0.06, 0.35, height, 8), mastMat);
            spire.position.set(x, yBottom + height / 2, z);
            parent.add(spire);

            // Red warning beacon bulb at top
            const beacon = new THREE.Mesh(new THREE.SphereGeometry(0.22, 8, 8), this.beaconRedMat);
            beacon.position.set(x, yBottom + height, z);
            parent.add(beacon);
            this.blinkingLeds.push(beacon);
        }

        buildDistantMountainRange(parent) {
            const mountainGroup = new THREE.Group();
            mountainGroup.name = 'distant_mountains';
            mountainGroup.position.set(0, -6, -145);

            this.mountainMat = new THREE.MeshStandardMaterial({
                color: this.isNight ? 0x0c172e : 0x3d6182,
                roughness: 0.95,
                metalness: 0.05,
                flatShading: true
            });

            this.mountainRidgeMat = new THREE.MeshStandardMaterial({
                color: this.isNight ? 0x1d2d44 : 0x7e9ebc,
                emissive: this.isNight ? 0x1e3a8a : 0x000000,
                emissiveIntensity: this.isNight ? 0.35 : 0,
                roughness: 0.9,
                flatShading: true
            });

            this.mountainHazeMat = new THREE.MeshBasicMaterial({
                color: this.isNight ? 0x070b14 : 0xcbe0f7,
                transparent: true,
                opacity: this.isNight ? 0.45 : 0.35,
                depthWrite: false
            });

            const createPeak = (x, z, h, r, segs = 9) => {
                const geom = new THREE.ConeGeometry(r, h, segs, 4);
                const posAttr = geom.attributes.position;
                for (let i = 0; i < posAttr.count; i++) {
                    const y = posAttr.getY(i);
                    if (y < h * 0.45 && y > -h * 0.45) {
                        const vx = posAttr.getX(i);
                        const vz = posAttr.getZ(i);
                        const factor = 1.0 + Math.sin(vx * 0.15 + vz * 0.2) * 0.12;
                        posAttr.setX(i, vx * factor);
                        posAttr.setZ(i, vz * factor);
                    }
                }
                geom.computeVertexNormals();
                const peak = new THREE.Mesh(geom, this.mountainMat);
                peak.position.set(x, h / 2, z);
                mountainGroup.add(peak);

                const capH = h * 0.35;
                const capR = r * 0.38;
                const capGeom = new THREE.ConeGeometry(capR, capH, segs, 2);
                capGeom.computeVertexNormals();
                const cap = new THREE.Mesh(capGeom, this.mountainRidgeMat);
                cap.position.set(x, h - capH / 2, z);
                mountainGroup.add(cap);
            };

            // Majestic Peaks across the horizon
            createPeak(15, 0, 52, 60, 9);
            createPeak(-38, 8, 44, 50, 8);
            createPeak(68, -6, 38, 45, 9);
            createPeak(-95, 12, 32, 40, 7);
            createPeak(115, -10, 30, 38, 7);

            // Rolling Foothills & Middle Ridges (Layer 2)
            const foothills = [
                { x: -65, z: 25, h: 22, r: 35 },
                { x: -15, z: 22, h: 20, r: 32 },
                { x: 38, z: 24, h: 24, r: 36 },
                { x: 88, z: 20, h: 18, r: 30 },
                { x: -115, z: 28, h: 16, r: 28 },
                { x: 135, z: 18, h: 17, r: 28 }
            ];
            foothills.forEach(f => {
                const fGeom = new THREE.ConeGeometry(f.r, f.h, 7);
                fGeom.computeVertexNormals();
                const foot = new THREE.Mesh(fGeom, this.mountainMat);
                foot.position.set(f.x, f.h / 2, f.z);
                mountainGroup.add(foot);
            });

            // Atmospheric Mist & Cloud Bands along mountain base (Layer 3)
            [-70, -25, 20, 65, 110].forEach(cx => {
                const cloud = new THREE.Mesh(new THREE.BoxGeometry(45, 4.5, 12), this.mountainHazeMat);
                cloud.position.set(cx, 8 + Math.sin(cx) * 2, 28);
                mountainGroup.add(cloud);
            });

            parent.add(mountainGroup);
        }

        buildSkyscraperTowerShaft(parent) {
            const shaftGroup = new THREE.Group();
            shaftGroup.name = 'tower_shaft_floors_1_to_19';

            this.towerFacadeMat = new THREE.MeshStandardMaterial({
                color: this.isNight ? 0x0b1120 : 0x1e293b,
                roughness: 0.7,
                metalness: 0.3
            });

            this.towerWindowsMat = new THREE.MeshStandardMaterial({
                color: this.isNight ? 0xfef08a : 0x93c5fd,
                emissive: this.isNight ? 0xfef08a : 0x38bdf8,
                emissiveIntensity: this.isNight ? 1.4 : 0.2,
                roughness: 0.2,
                metalness: 0.8
            });

            const finMat = new THREE.MeshStandardMaterial({ color: 0x0f172a, roughness: 0.4, metalness: 0.85 });

            // Main Tower Shaft Body (55m tall, from y = 0 down to y = -55)
            const shaftH = 55;
            const shaft = new THREE.Mesh(new THREE.BoxGeometry(52, shaftH, 30), this.towerFacadeMat);
            shaft.position.set(0, -shaftH / 2, -2.5);
            shaftGroup.add(shaft);

            // Floor Division Spandrels & Horizontal Window Bands for Floors 1 to 19
            for (let f = 1; f <= 16; f++) {
                const fy = -f * 3.2;
                const winRibbon = new THREE.Mesh(new THREE.BoxGeometry(52.2, 1.4, 30.2), this.towerWindowsMat);
                winRibbon.position.set(0, fy, -2.5);
                shaftGroup.add(winRibbon);

                const spandrel = new THREE.Mesh(new THREE.BoxGeometry(52.4, 0.45, 30.4), finMat);
                spandrel.position.set(0, fy + 0.9, -2.5);
                shaftGroup.add(spandrel);
            }

            // Vertical Architectural Accent Fins (Mullions) on Front Facade
            [-22, -15, -8, 0, 8, 15, 22].forEach(fx => {
                const fin = new THREE.Mesh(new THREE.BoxGeometry(0.3, shaftH, 0.6), finMat);
                fin.position.set(fx, -shaftH / 2, 12.6);
                shaftGroup.add(fin);
            });

            // Illuminated Building Crest Sign below floor 20
            this.createNeonSignText(shaftGroup, 0, -2.2, 12.8, 'COOCA TOWER // 20F', 0x38bdf8, 7.5);

            parent.add(shaftGroup);
        }

        buildRooftopSkyTerrace(parent) {
            const terraceGroup = new THREE.Group();
            terraceGroup.name = 'rooftop_sky_terrace_floor_20';

            const deckMat = new THREE.MeshStandardMaterial({
                color: 0x1e293b,
                roughness: 0.85,
                metalness: 0.1
            });
            const paverMat = new THREE.MeshStandardMaterial({
                color: 0x334155,
                roughness: 0.7,
                metalness: 0.2
            });
            const handrailMat = new THREE.MeshStandardMaterial({
                color: 0xe2e8f0,
                metalness: 0.95,
                roughness: 0.15
            });
            const balustradeGlassMat = new THREE.MeshPhysicalMaterial({
                color: 0x93c5fd,
                transparent: true,
                opacity: 0.35,
                roughness: 0.1,
                transmission: 0.85
            });

            // 1. Terrace Deck Floor (Z = 11.0 to 24.5, X = -25.5 to 25.5, thickness 0.4)
            const deck = new THREE.Mesh(new THREE.BoxGeometry(51, 0.4, 13.5), deckMat);
            deck.position.set(0, -0.2, 17.75);
            deck.receiveShadow = true;
            terraceGroup.add(deck);

            // Center Walkway in Stone Pavers
            const walkway = new THREE.Mesh(new THREE.BoxGeometry(10.0, 0.02, 13.0), paverMat);
            walkway.position.set(0, 0.02, 17.75);
            terraceGroup.add(walkway);

            // 2. Perimeter Safety Glass Balustrade & Handrail
            // A. South Edge (Z = 24.5, length = 51)
            const sGlass = new THREE.Mesh(new THREE.BoxGeometry(51, 1.25, 0.06), balustradeGlassMat);
            sGlass.position.set(0, 0.65, 24.5);
            terraceGroup.add(sGlass);

            const sRail = new THREE.Mesh(new THREE.CylinderGeometry(0.04, 0.04, 51.1, 12), handrailMat);
            sRail.rotation.z = Math.PI / 2;
            sRail.position.set(0, 1.28, 24.5);
            terraceGroup.add(sRail);

            for (let bx = -25; bx <= 25; bx += 5.0) {
                const post = new THREE.Mesh(new THREE.BoxGeometry(0.08, 1.3, 0.08), handrailMat);
                post.position.set(bx, 0.65, 24.5);
                terraceGroup.add(post);
            }

            // B. West Terrace Edge (X = -25.5, Z = 11.0 to 24.5, length = 13.5)
            const wGlass = new THREE.Mesh(new THREE.BoxGeometry(0.06, 1.25, 13.5), balustradeGlassMat);
            wGlass.position.set(-25.5, 0.65, 17.75);
            terraceGroup.add(wGlass);

            const wRail = new THREE.Mesh(new THREE.CylinderGeometry(0.04, 0.04, 13.6, 12), handrailMat);
            wRail.rotation.x = Math.PI / 2;
            wRail.position.set(-25.5, 1.28, 17.75);
            terraceGroup.add(wRail);

            // C. East Terrace Edge (X = 25.5, Z = 11.0 to 24.5, length = 13.5)
            const eGlass = new THREE.Mesh(new THREE.BoxGeometry(0.06, 1.25, 13.5), balustradeGlassMat);
            eGlass.position.set(25.5, 0.65, 17.75);
            terraceGroup.add(eGlass);

            const eRail = new THREE.Mesh(new THREE.CylinderGeometry(0.04, 0.04, 13.6, 12), handrailMat);
            eRail.rotation.x = Math.PI / 2;
            eRail.position.set(25.5, 1.28, 17.75);
            terraceGroup.add(eRail);

            // 3. Executive Rooftop Helipad (x = 13.5, z = 17.75)
            this.buildRooftopHelipad(terraceGroup, 13.5, 17.75);

            // 4. Sky Terrace Lounge Sofas & Planters (x = -13.5, z = 17.75)
            this.buildSkyTerraceLounge(terraceGroup, -13.5, 17.75);

            // 5. Sky Terrace Life (Executives, Sightseers, Pedestrians)
            this.buildSkyTerraceLife(terraceGroup);

            parent.add(terraceGroup);
        }

        buildRooftopHelipad(parent, x, z) {
            const padGroup = new THREE.Group();
            padGroup.position.set(x, 0.02, z);

            const padGeom = new THREE.CylinderGeometry(4.2, 4.3, 0.08, 8);
            const padMat = new THREE.MeshStandardMaterial({ color: 0x1e2430, roughness: 0.85 });
            const pad = new THREE.Mesh(padGeom, padMat);
            pad.receiveShadow = true;
            padGroup.add(pad);

            const ringMat = new THREE.MeshBasicMaterial({ color: 0xfacc15 });
            const ring = new THREE.Mesh(new THREE.RingGeometry(3.6, 3.8, 32), ringMat);
            ring.rotation.x = -Math.PI / 2;
            ring.position.y = 0.05;
            padGroup.add(ring);

            const hMat = new THREE.MeshBasicMaterial({ color: 0xffffff });
            const hBarL = new THREE.Mesh(new THREE.PlaneGeometry(0.4, 2.4), hMat);
            hBarL.rotation.x = -Math.PI / 2;
            hBarL.position.set(-0.7, 0.055, 0);
            padGroup.add(hBarL);

            const hBarR = new THREE.Mesh(new THREE.PlaneGeometry(0.4, 2.4), hMat);
            hBarR.rotation.x = -Math.PI / 2;
            hBarR.position.set(0.7, 0.055, 0);
            padGroup.add(hBarR);

            const hBarM = new THREE.Mesh(new THREE.PlaneGeometry(1.4, 0.4), hMat);
            hBarM.rotation.x = -Math.PI / 2;
            hBarM.position.set(0, 0.055, 0);
            padGroup.add(hBarM);

            const beaconMat = new THREE.MeshBasicMaterial({ color: 0x38bdf8 });
            for (let a = 0; a < 8; a++) {
                const angle = a * (Math.PI / 4);
                const bx = Math.cos(angle) * 4.0;
                const bz = Math.sin(angle) * 4.0;
                const bMesh = new THREE.Mesh(new THREE.CylinderGeometry(0.08, 0.08, 0.12, 8), beaconMat);
                bMesh.position.set(bx, 0.06, bz);
                padGroup.add(bMesh);
            }

            parent.add(padGroup);
        }

        buildSkyTerraceLounge(parent, x, z) {
            const loungeGroup = new THREE.Group();
            loungeGroup.position.set(x, 0, z);

            const fabricMat = new THREE.MeshStandardMaterial({ color: 0x334155, roughness: 0.8 });
            const woodMat = new THREE.MeshStandardMaterial({ color: 0x78350f, roughness: 0.4 });
            const planterMat = new THREE.MeshStandardMaterial({ color: 0x0f172a, roughness: 0.5 });
            const leafMat = new THREE.MeshStandardMaterial({ color: 0x15803d, roughness: 0.6 });

            const sofaBase1 = new THREE.Mesh(new THREE.BoxGeometry(4.2, 0.35, 1.2), fabricMat);
            sofaBase1.position.set(0, 0.25, 0);
            loungeGroup.add(sofaBase1);

            const sofaBack1 = new THREE.Mesh(new THREE.BoxGeometry(4.2, 0.5, 0.3), fabricMat);
            sofaBack1.position.set(0, 0.55, -0.45);
            loungeGroup.add(sofaBack1);

            const table = new THREE.Mesh(new THREE.BoxGeometry(2.4, 0.25, 1.0), woodMat);
            table.position.set(0, 0.2, 1.4);
            loungeGroup.add(table);

            [-2.6, 2.6].forEach(px => {
                const planter = new THREE.Mesh(new THREE.BoxGeometry(0.8, 0.7, 2.2), planterMat);
                planter.position.set(px, 0.35, 0.5);
                loungeGroup.add(planter);

                const bush = new THREE.Mesh(new THREE.SphereGeometry(0.55, 8, 8), leafMat);
                bush.position.set(px, 0.85, 0.5);
                bush.scale.set(0.8, 0.6, 1.8);
                loungeGroup.add(bush);
            });

            parent.add(loungeGroup);
        }

        buildSouthExteriorFacade(parent) {
            const facadeGroup = new THREE.Group();
            facadeGroup.position.set(0, 0, 11.0);

            const wallMat = new THREE.MeshStandardMaterial({ color: 0x1e2430, roughness: 0.5 });
            const frameMat = new THREE.MeshStandardMaterial({ color: 0x0f172a, roughness: 0.3, metalness: 0.8 });
            const glassMat = new THREE.MeshPhysicalMaterial({
                color: 0x93c5fd,
                transparent: true,
                opacity: 0.35,
                roughness: 0.1,
                transmission: 0.85
            });

            // --- 1. WEST FRONT FACADE (x = -25.0 to -4.5, width = 20.5, center = -14.75) ---
            // Lower Kickplate
            const wKick = new THREE.Mesh(new THREE.BoxGeometry(20.5, 0.6, 0.4), wallMat);
            wKick.position.set(-14.75, 0.3, 0);
            facadeGroup.add(wKick);

            // Upper Spandrel Beam
            const wSpandrel = new THREE.Mesh(new THREE.BoxGeometry(20.5, 0.8, 0.4), wallMat);
            wSpandrel.position.set(-14.75, 4.6, 0);
            facadeGroup.add(wSpandrel);

            // Glass Curtain Panels
            const wGlass = new THREE.Mesh(new THREE.BoxGeometry(20.3, 3.7, 0.08), glassMat);
            wGlass.position.set(-14.75, 2.45, 0);
            facadeGroup.add(wGlass);

            // Vertical Mullions on West Facade
            for (let mx = -23.0; mx <= -6.5; mx += 3.8) {
                const mullion = new THREE.Mesh(new THREE.BoxGeometry(0.12, 3.7, 0.15), frameMat);
                mullion.position.set(mx, 2.45, 0);
                facadeGroup.add(mullion);
            }

            // --- 2. EAST FRONT FACADE (x = 4.5 to 25.0, width = 20.5, center = 14.75) ---
            // Lower Kickplate
            const eKick = new THREE.Mesh(new THREE.BoxGeometry(20.5, 0.6, 0.4), wallMat);
            eKick.position.set(14.75, 0.3, 0);
            facadeGroup.add(eKick);

            // Upper Spandrel Beam
            const eSpandrel = new THREE.Mesh(new THREE.BoxGeometry(20.5, 0.8, 0.4), wallMat);
            eSpandrel.position.set(14.75, 4.6, 0);
            facadeGroup.add(eSpandrel);

            // Glass Curtain Panels
            const eGlass = new THREE.Mesh(new THREE.BoxGeometry(20.3, 3.7, 0.08), glassMat);
            eGlass.position.set(14.75, 2.45, 0);
            facadeGroup.add(eGlass);

            // Vertical Mullions on East Facade
            for (let mx = 6.5; mx <= 23.0; mx += 3.8) {
                const mullion = new THREE.Mesh(new THREE.BoxGeometry(0.12, 3.7, 0.15), frameMat);
                mullion.position.set(mx, 2.45, 0);
                facadeGroup.add(mullion);
            }

            // --- 3. CENTRAL GRAND ENTRANCE PORTAL (x = -4.5 to 4.5, width = 9.0, center = 0) ---
            // Portal Header
            const portalHeader = new THREE.Mesh(new THREE.BoxGeometry(9.0, 1.2, 0.5), wallMat);
            portalHeader.position.set(0, 4.4, 0);
            facadeGroup.add(portalHeader);

            // Upper Glass Transom above doors
            const transomGlass = new THREE.Mesh(new THREE.BoxGeometry(8.8, 1.0, 0.08), glassMat);
            transomGlass.position.set(0, 3.3, 0);
            facadeGroup.add(transomGlass);

            // Architectural Overhang / Canopy projecting forward outside
            const canopy = new THREE.Mesh(new THREE.BoxGeometry(9.4, 0.22, 2.6), frameMat);
            canopy.position.set(0, 3.75, 1.25);
            canopy.castShadow = true;
            facadeGroup.add(canopy);

            // Canopy Neon Fascia Sign
            this.createNeonSignText(facadeGroup, 0, 3.85, 2.55, 'COOCA', 0x38bdf8, 5.0);

            // Double Sliding Glass Doors at Ground Level (y = 1.4, h = 2.8)
            const doorMat = new THREE.MeshPhysicalMaterial({
                color: 0x93c5fd,
                transparent: true,
                opacity: 0.45,
                roughness: 0.08,
                transmission: 0.9
            });
            const doorL = new THREE.Mesh(new THREE.BoxGeometry(2.1, 2.7, 0.06), doorMat);
            doorL.position.set(-1.15, 1.35, 0);
            facadeGroup.add(doorL);

            const doorR = new THREE.Mesh(new THREE.BoxGeometry(2.1, 2.7, 0.06), doorMat);
            doorR.position.set(1.15, 1.35, 0);
            facadeGroup.add(doorR);

            // Stainless Steel Door Handles
            const handleMat = new THREE.MeshStandardMaterial({ color: 0xe2e8f0, metalness: 0.9, roughness: 0.1 });
            [-0.15, 0.15].forEach(hx => {
                const handle = new THREE.Mesh(new THREE.CylinderGeometry(0.02, 0.02, 1.2), handleMat);
                handle.position.set(hx, 1.35, 0.08);
                facadeGroup.add(handle);
            });

            // Door Frame Pillars
            [-4.5, -2.25, 2.25, 4.5].forEach(fx => {
                const framePillar = new THREE.Mesh(new THREE.BoxGeometry(0.12, 2.8, 0.16), frameMat);
                framePillar.position.set(fx, 1.4, 0);
                facadeGroup.add(framePillar);
            });

            parent.add(facadeGroup);
        }

        buildOneFloorGlassPartitions(parent) {
            this.wallColliders = [];
            this.doors.clear();

            // =========================================================================
            // ARCHITECTURAL FROSTED BLUR GLASS ("Kaca Blur / Frosted Satin Glass")
            // Translucent glass with blur diffusion roughness, refraction depth,
            // eye-level privacy banding, and anodized slate mullion frames.
            // =========================================================================
            const createFrostedGlassMaterial = (tintColorHex, attenuationHex, opacity = 0.82) => {
                return new THREE.MeshPhysicalMaterial({
                    color: tintColorHex,
                    transparent: true,
                    opacity: opacity,
                    roughness: 0.62,           // HIGH ROUGHNESS gives the frosted / blur effect
                    metalness: 0.05,
                    transmission: 0.86,        // High transmission allows background to blur through
                    thickness: 1.4,            // Simulates thick architectural glass
                    ior: 1.46,                 // Standard architectural glass index of refraction
                    attenuationColor: new THREE.Color(attenuationHex),
                    attenuationDistance: 0.95,
                    clearcoat: 0.28,           // Exterior silky reflection
                    clearcoatRoughness: 0.12,
                    depthWrite: false,         // Essential for proper sorting of transparent office elements
                    side: THREE.DoubleSide
                });
            };

            const glassMat = createFrostedGlassMaterial(0xdbeafe, 0x93c5fd, 0.84);        // Frost Icy Blue
            const purpleGlassMat = createFrostedGlassMaterial(0xf3e8ff, 0xc084fc, 0.84); // Frost Lavender / Growth
            const tealGlassMat = createFrostedGlassMaterial(0xccfbf1, 0x2dd4bf, 0.84);   // Frost Mint / Operations
            const amberGlassMat = createFrostedGlassMaterial(0xfef3c7, 0xf59e0b, 0.84);  // Frost Warm Gold / Finance
            
            // Solid Acoustic Architectural Drywall Material for Restroom (100% Solid Opaque Slate, No Glass)
            const solidRestroomWallMat = new THREE.MeshStandardMaterial({
                color: 0x334155,
                roughness: 0.88,
                metalness: 0.05
            });

            const frameMat = new THREE.MeshStandardMaterial({
                color: 0x0f172a,
                metalness: 0.85,
                roughness: 0.25
            });

            // 1. Central Spine Perimeter Dividers:
            // West Corridor Wall (separating West Wing from Atrium): x = -7.2, z: -4.2 to 10.75, Doorway at z = -0.5 (growth_door)
            this.createPartitionWithDoorway(parent, -7.2, -4.2, 10.75, -0.5, 2.2, 3.8, frameMat, purpleGlassMat, 'along_z', 'door_growth', false);

            // East Corridor Wall (separating East Wing from Atrium):
            // Part A: Operations & Finance Entry from Atrium: x = 7.2, z: -4.2 to 1.5, Doorway at z = -0.5 (ops_door)
            this.createPartitionWithDoorway(parent, 7.2, -4.2, 1.5, -0.5, 2.2, 3.8, frameMat, tealGlassMat, 'along_z', 'door_ops', false);

            // Part B: Direct Dedicated Entry into Pantry & Bistro Lounge from Atrium: x = 7.2, z: 1.5 to 6.8, Doorway at z = 4.0 (door_pantry_atrium)
            // (Completely bypasses Finance room - no need to walk through finance!)
            this.createPartitionWithDoorway(parent, 7.2, 1.5, 6.8, 4.0, 2.2, 3.8, frameMat, tealGlassMat, 'along_z', 'door_pantry_atrium', false);

            // Part C: Solid Wall separating Restroom from Atrium/Entrance: x = 7.2, z: 6.8 to 10.75 (100% Solid Opaque Wall, No Glass)
            this.createSolidPartition(parent, 7.2, 6.8, 10.75, 3.8, frameMat, solidRestroomWallMat, 'along_z', true);

            // Executive Suite Front Wall (separating Executive Suite from Atrium): z = -4.2, x: -7.2 to 7.2, Double Doorway at x = 0 (exec_door)
            this.createPartitionWithDoorway(parent, -4.2, -7.2, 7.2, 0, 2.8, 3.8, frameMat, glassMat, 'along_x', 'door_exec', true);

            // Reception / Atrium Portal: z = 5.5, x: -7.2 to 7.2, Grand Double Door at x = 0
            this.createPartitionWithDoorway(parent, 5.5, -7.2, 7.2, 0, 3.4, 3.8, frameMat, glassMat, 'along_x', 'door_atrium', true);

            // 2. North Boundary Partitions separating Executive Office, Marketing, and Operations:
            // Divider between Marketing and Executive Suite: x = -7.2, z: -15.75 to -4.2, Doorway at z = -9.5 (door_mkt_exec)
            this.createPartitionWithDoorway(parent, -7.2, -15.75, -4.2, -9.5, 2.0, 3.8, frameMat, purpleGlassMat, 'along_z', 'door_mkt_exec', false);

            // Divider between Operations and Executive Suite: x = 7.2, z: -15.75 to -4.2, Doorway at z = -9.5 (door_exec_ops)
            this.createPartitionWithDoorway(parent, 7.2, -15.75, -4.2, -9.5, 2.0, 3.8, frameMat, tealGlassMat, 'along_z', 'door_exec_ops', false);

            // 3. West Wing Internal Partitions:
            // Divider between Marketing and Sales: z = -5.0, x: -24.75 to -7.2, Doorway at x = -10.5 (mkt_door)
            this.createPartitionWithDoorway(parent, -5.0, -24.75, -7.2, -10.5, 2.2, 3.8, frameMat, purpleGlassMat, 'along_x', 'door_mkt', false);

            // Divider between Sales and Meeting Room: z = 2.5, x: -24.75 to -7.2, Doorway at x = -10.5 (meeting_door)
            this.createPartitionWithDoorway(parent, 2.5, -24.75, -7.2, -10.5, 2.2, 3.8, frameMat, purpleGlassMat, 'along_x', 'door_meeting', false);

            // 4. East Wing Internal Partitions:
            // Divider between Operations and Server Room: x = 18.0, z: -15.75 to -5.0, Doorway at z = -10.5 (server_door)
            this.createPartitionWithDoorway(parent, 18.0, -15.75, -5.0, -10.5, 1.8, 3.8, frameMat, tealGlassMat, 'along_z', 'door_server', false);

            // Divider between Operations/Server and Finance/Docs: z = -5.0, x: 7.2 to 24.75, Doorway at x = 10.5 (ops_room_door)
            this.createPartitionWithDoorway(parent, -5.0, 7.2, 24.75, 10.5, 2.2, 3.8, frameMat, tealGlassMat, 'along_x', 'door_ops_room', false);

            // Divider between Finance and Document Archive: x = 18.0, z: -5.0 to 1.5, Doorway at z = -1.8 (doc_door)
            this.createPartitionWithDoorway(parent, 18.0, -5.0, 1.5, -1.8, 1.8, 3.8, frameMat, amberGlassMat, 'along_z', 'door_doc', false);

            // SOLID Divider between Finance/Docs and Pantry/Lounge (z = 1.5, x: 7.2 to 24.75):
            // (Completely seals Finance so NO traffic passes through Finance to get to Pantry!)
            this.createSolidPartition(parent, 1.5, 7.2, 24.75, 3.8, frameMat, amberGlassMat, 'along_x', true);

            // Internal Divider between Pantry Bistro Lounge and Pantry Dining Area: x = 15.5, z: 1.5 to 6.8, Doorway at z = 4.2 (door_pantry_inner)
            this.createPartitionWithDoorway(parent, 15.5, 1.5, 6.8, 4.2, 2.0, 3.8, frameMat, tealGlassMat, 'along_z', 'door_pantry_inner', false);

            // Solid Partition with Doorway separating Pantry and Restroom: z = 6.8, x: 7.2 to 24.75, Solid Doorway at x = 19.5 (door_restroom)
            // (100% Solid Opaque Partition Wall & Solid Core Door - Zero Glass for Complete Privacy)
            this.createPartitionWithDoorway(parent, 6.8, 7.2, 24.75, 19.5, 1.8, 3.8, frameMat, solidRestroomWallMat, 'along_x', 'door_restroom', false, true);
        }

        createPartitionWithDoorway(parent, fixedCoord, startCoord, endCoord, doorCenter, doorWidth, height, frameMat, glassMat, orientation = 'along_x', doorId = null, isDouble = false, isSolid = false) {
            const group = new THREE.Group();
            const thick = 0.08;
            const halfDoor = doorWidth / 2;

            const seg1Start = Math.min(startCoord, endCoord);
            const seg1End = doorCenter - halfDoor;
            const seg1Len = Math.max(0, seg1End - seg1Start);

            const seg2Start = doorCenter + halfDoor;
            const seg2End = Math.max(startCoord, endCoord);
            const seg2Len = Math.max(0, seg2End - seg2Start);

            // Privacy band material (frosted horizontal modesty strip at eye level: y = 1.05 to 1.55)
            const privacyBandMat = new THREE.MeshPhysicalMaterial({
                color: glassMat.color ? glassMat.color.getHex() : 0xe2e8f0,
                transparent: true,
                opacity: 0.92,
                roughness: 0.72,
                transmission: 0.60,
                thickness: 1.5,
                ior: 1.48,
                depthWrite: false,
                side: THREE.DoubleSide
            });

            // Door leaf materials
            const doorFrameMat = new THREE.MeshStandardMaterial({
                color: 0x0f172a,
                metalness: 0.85,
                roughness: 0.25
            });
            const doorGlassMat = new THREE.MeshPhysicalMaterial({
                color: 0xf1f5f9,
                transparent: true,
                opacity: 0.85,
                roughness: 0.55,
                transmission: 0.88,
                thickness: 1.3,
                ior: 1.46,
                clearcoat: 0.35,
                clearcoatRoughness: 0.1,
                depthWrite: false,
                side: THREE.DoubleSide
            });
            const handleMat = new THREE.MeshStandardMaterial({
                color: 0xf8fafc,
                metalness: 0.95,
                roughness: 0.15
            });

            const wallHalfThick = 0.22; // Collider thickness padding
            const doorH = Math.min(height - 0.2, 2.7);

            if (orientation === 'along_x') {
                const z = fixedCoord;

                // --- 1. SEGMENT 1 (Left of Door) ---
                if (seg1Len > 0.1) {
                    const c1 = (seg1Start + seg1End) / 2;
                    if (isSolid) {
                        const solidWall = new THREE.Mesh(new THREE.BoxGeometry(seg1Len, height, thick), glassMat);
                        solidWall.position.set(c1, height / 2, z);
                        solidWall.castShadow = true;
                        solidWall.receiveShadow = true;
                        group.add(solidWall);

                        const kick1 = new THREE.Mesh(new THREE.BoxGeometry(seg1Len, 0.12, thick + 0.02), frameMat);
                        kick1.position.set(c1, 0.06, z);
                        group.add(kick1);

                        const topTrim1 = new THREE.Mesh(new THREE.BoxGeometry(seg1Len, 0.08, thick + 0.02), frameMat);
                        topTrim1.position.set(c1, height - 0.04, z);
                        group.add(topTrim1);
                    } else {
                        const g1 = new THREE.Mesh(new THREE.BoxGeometry(seg1Len, height, thick), glassMat);
                        g1.position.set(c1, height / 2, z);
                        group.add(g1);

                        const pb1 = new THREE.Mesh(new THREE.BoxGeometry(seg1Len, 0.5, thick + 0.005), privacyBandMat);
                        pb1.position.set(c1, 1.3, z);
                        group.add(pb1);

                        [1.05, 1.55].forEach(my => {
                            const mLine = new THREE.Mesh(new THREE.BoxGeometry(seg1Len, 0.025, thick + 0.015), frameMat);
                            mLine.position.set(c1, my, z);
                            group.add(mLine);
                        });

                        const kick1 = new THREE.Mesh(new THREE.BoxGeometry(seg1Len, 0.1, thick + 0.02), frameMat);
                        kick1.position.set(c1, 0.05, z);
                        group.add(kick1);
                    }

                    // Register Solid Wall Collider
                    this.wallColliders.push({
                        minX: seg1Start,
                        maxX: seg1End,
                        minZ: z - wallHalfThick,
                        maxZ: z + wallHalfThick
                    });
                }

                // --- 2. SEGMENT 2 (Right of Door) ---
                if (seg2Len > 0.1) {
                    const c2 = (seg2Start + seg2End) / 2;
                    if (isSolid) {
                        const solidWall = new THREE.Mesh(new THREE.BoxGeometry(seg2Len, height, thick), glassMat);
                        solidWall.position.set(c2, height / 2, z);
                        solidWall.castShadow = true;
                        solidWall.receiveShadow = true;
                        group.add(solidWall);

                        const kick2 = new THREE.Mesh(new THREE.BoxGeometry(seg2Len, 0.12, thick + 0.02), frameMat);
                        kick2.position.set(c2, 0.06, z);
                        group.add(kick2);

                        const topTrim2 = new THREE.Mesh(new THREE.BoxGeometry(seg2Len, 0.08, thick + 0.02), frameMat);
                        topTrim2.position.set(c2, height - 0.04, z);
                        group.add(topTrim2);
                    } else {
                        const g2 = new THREE.Mesh(new THREE.BoxGeometry(seg2Len, height, thick), glassMat);
                        g2.position.set(c2, height / 2, z);
                        group.add(g2);

                        const pb2 = new THREE.Mesh(new THREE.BoxGeometry(seg2Len, 0.5, thick + 0.005), privacyBandMat);
                        pb2.position.set(c2, 1.3, z);
                        group.add(pb2);

                        [1.05, 1.55].forEach(my => {
                            const mLine = new THREE.Mesh(new THREE.BoxGeometry(seg2Len, 0.025, thick + 0.015), frameMat);
                            mLine.position.set(c2, my, z);
                            group.add(mLine);
                        });

                        const kick2 = new THREE.Mesh(new THREE.BoxGeometry(seg2Len, 0.1, thick + 0.02), frameMat);
                        kick2.position.set(c2, 0.05, z);
                        group.add(kick2);
                    }

                    // Register Solid Wall Collider
                    this.wallColliders.push({
                        minX: seg2Start,
                        maxX: seg2End,
                        minZ: z - wallHalfThick,
                        maxZ: z + wallHalfThick
                    });
                }

                // Door Lintel / Top Beam above doorway
                const totalLen = Math.abs(endCoord - startCoord);
                const topBar = new THREE.Mesh(new THREE.BoxGeometry(totalLen, 0.12, thick + 0.04), frameMat);
                topBar.position.set((startCoord + endCoord) / 2, height, z);
                group.add(topBar);

                // Transom bar directly above door head
                const transomBar = new THREE.Mesh(new THREE.BoxGeometry(doorWidth, 0.08, thick + 0.03), frameMat);
                transomBar.position.set(doorCenter, doorH, z);
                group.add(transomBar);

                // If solid wall, close the space between door head (doorH) and ceiling (height) with solid panel
                if (isSolid) {
                    const transomH = height - doorH;
                    const solidTransom = new THREE.Mesh(new THREE.BoxGeometry(doorWidth, transomH, thick), glassMat);
                    solidTransom.position.set(doorCenter, doorH + transomH / 2, z);
                    solidTransom.castShadow = true;
                    solidTransom.receiveShadow = true;
                    group.add(solidTransom);
                }

                // Door posts (vertical frame pillars at doorway sides)
                [doorCenter - halfDoor, doorCenter + halfDoor].forEach(px => {
                    const post = new THREE.Mesh(new THREE.BoxGeometry(0.08, height, thick + 0.04), frameMat);
                    post.position.set(px, height / 2, z);
                    group.add(post);
                });

                // Floor transition threshold sill
                const sill = new THREE.Mesh(new THREE.BoxGeometry(doorWidth, 0.015, 0.18), frameMat);
                sill.position.set(doorCenter, 0.008, z);
                group.add(sill);

                // --- 3. CONSTRUCT 3D INTERACTIVE DOOR(S) ---
                const doorMaster = new THREE.Group();
                let leftPivot = null;
                let rightPivot = null;

                const makeDoorLeafX = (leafWidth, hingeSide) => {
                    const leaf = new THREE.Group();
                    const leafThick = 0.05;

                    if (isSolid) {
                        // 100% Solid Core Door Panel (No glass!)
                        const solidLeaf = new THREE.Mesh(new THREE.BoxGeometry(leafWidth, doorH, leafThick), doorFrameMat);
                        solidLeaf.position.set(0, 0, 0);
                        solidLeaf.castShadow = true;
                        leaf.add(solidLeaf);

                        // Solid Inlay panel
                        const solidPanelMat = new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.65 });
                        const coreInlay = new THREE.Mesh(new THREE.BoxGeometry(leafWidth - 0.06, doorH - 0.08, leafThick + 0.005), solidPanelMat);
                        coreInlay.position.set(0, 0, 0);
                        leaf.add(coreInlay);

                        // Plaque on door with Restroom text / icon
                        const plaqueMat = new THREE.MeshStandardMaterial({ color: 0x0f172a, roughness: 0.3 });
                        const plaque = new THREE.Mesh(new THREE.BoxGeometry(0.32, 0.20, leafThick + 0.015), plaqueMat);
                        plaque.position.set(0, 1.55 - doorH / 2, 0);
                        leaf.add(plaque);

                        // Privacy Indicator Lock (Green = Vacant)
                        const dotMat = new THREE.MeshBasicMaterial({ color: 0x10b981 });
                        [-leafThick / 2 - 0.01, leafThick / 2 + 0.01].forEach(dz => {
                            const dot = new THREE.Mesh(new THREE.CircleGeometry(0.025, 16), dotMat);
                            dot.position.set(0, 1.35 - doorH / 2, dz);
                            if (dz < 0) dot.rotation.y = Math.PI;
                            leaf.add(dot);
                        });

                        // Stainless Steel Bottom Kickplate
                        const kickMat = new THREE.MeshStandardMaterial({ color: 0xe2e8f0, metalness: 0.9, roughness: 0.15 });
                        const kickPlate = new THREE.Mesh(new THREE.BoxGeometry(leafWidth - 0.02, 0.22, leafThick + 0.01), kickMat);
                        kickPlate.position.set(0, 0.11 - doorH / 2, 0);
                        leaf.add(kickPlate);

                    } else {
                        // Leaf outer frame
                        const fTop = new THREE.Mesh(new THREE.BoxGeometry(leafWidth, 0.06, leafThick), doorFrameMat);
                        fTop.position.set(0, doorH / 2 - 0.03, 0);
                        leaf.add(fTop);

                        const fBottom = new THREE.Mesh(new THREE.BoxGeometry(leafWidth, 0.12, leafThick), doorFrameMat);
                        fBottom.position.set(0, -doorH / 2 + 0.06, 0);
                        leaf.add(fBottom);

                        const fLeft = new THREE.Mesh(new THREE.BoxGeometry(0.06, doorH, leafThick), doorFrameMat);
                        fLeft.position.set(-leafWidth / 2 + 0.03, 0, 0);
                        leaf.add(fLeft);

                        const fRight = new THREE.Mesh(new THREE.BoxGeometry(0.06, doorH, leafThick), doorFrameMat);
                        fRight.position.set(leafWidth / 2 - 0.03, 0, 0);
                        leaf.add(fRight);

                        // Frosted glass center panel
                        const gPane = new THREE.Mesh(new THREE.BoxGeometry(leafWidth - 0.1, doorH - 0.16, 0.03), doorGlassMat);
                        leaf.add(gPane);

                        // Privacy band in center of door
                        const dBand = new THREE.Mesh(new THREE.BoxGeometry(leafWidth - 0.1, 0.5, 0.032), privacyBandMat);
                        dBand.position.set(0, 1.3 - doorH / 2, 0);
                        leaf.add(dBand);
                    }

                    // Pull handle hardware (vertical stainless steel bar on both sides)
                    const handleX = hingeSide === 'left' ? (leafWidth / 2 - 0.12) : (-leafWidth / 2 + 0.12);
                    const handleY = 1.05 - doorH / 2;

                    [-0.045, 0.045].forEach(sideZ => {
                        const bar = new THREE.Mesh(new THREE.CylinderGeometry(0.016, 0.016, 0.72, 16), handleMat);
                        bar.position.set(handleX, handleY, sideZ);
                        leaf.add(bar);

                        // Mount brackets
                        [-0.28, 0.28].forEach(by => {
                            const standoff = new THREE.Mesh(new THREE.CylinderGeometry(0.01, 0.01, 0.04, 12), handleMat);
                            standoff.rotation.x = Math.PI / 2;
                            standoff.position.set(handleX, handleY + by, sideZ / 2);
                            leaf.add(standoff);
                        });
                    });

                    return leaf;
                };

                if (isDouble) {
                    const leafW = (doorWidth - 0.1) / 2;

                    // Left leaf & pivot
                    leftPivot = new THREE.Group();
                    leftPivot.position.set(doorCenter - halfDoor + 0.04, 0, z);
                    const leftLeaf = makeDoorLeafX(leafW, 'left');
                    leftLeaf.position.set(leafW / 2, doorH / 2, 0);
                    leftPivot.add(leftLeaf);
                    doorMaster.add(leftPivot);

                    // Right leaf & pivot
                    rightPivot = new THREE.Group();
                    rightPivot.position.set(doorCenter + halfDoor - 0.04, 0, z);
                    const rightLeaf = makeDoorLeafX(leafW, 'right');
                    rightLeaf.position.set(-leafW / 2, doorH / 2, 0);
                    rightPivot.add(rightLeaf);
                    doorMaster.add(rightPivot);

                } else {
                    const leafW = doorWidth - 0.08;

                    // Single leaf hinged on left
                    leftPivot = new THREE.Group();
                    leftPivot.position.set(doorCenter - halfDoor + 0.04, 0, z);
                    const singleLeaf = makeDoorLeafX(leafW, 'left');
                    singleLeaf.position.set(leafW / 2, doorH / 2, 0);
                    leftPivot.add(singleLeaf);
                    doorMaster.add(leftPivot);
                }

                group.add(doorMaster);

                // Closed Door Collider Box (Blocks entrance when door is closed)
                const doorCollider = {
                    minX: doorCenter - halfDoor + 0.05,
                    maxX: doorCenter + halfDoor - 0.05,
                    minZ: z - wallHalfThick,
                    maxZ: z + wallHalfThick
                };

                if (!doorId) doorId = `door_${this.doors.size + 1}`;

                // Tag for raycasting & interactive clicks
                doorMaster.traverse((child) => {
                    if (child.isMesh) {
                        child.userData = { doorId: doorId, isDoor: true };
                        this.interactiveObjects.push(child);
                    }
                });

                this.doors.set(doorId, {
                    id: doorId,
                    type: isDouble ? 'double' : 'single',
                    orientation: 'along_x',
                    center: new THREE.Vector3(doorCenter, 0, z),
                    fixedCoord: z,
                    width: doorWidth,
                    height: doorH,
                    leftPivot: leftPivot,
                    rightPivot: rightPivot,
                    currentAngle: 0,
                    targetAngle: 0,
                    maxAngle: isDouble ? 1.45 : 1.40,
                    state: 'closed',
                    collider: doorCollider,
                    manualLockUntil: 0,
                    masterGroup: doorMaster
                });

            } else {
                // orientation === 'along_z'
                const x = fixedCoord;

                // --- 1. SEGMENT 1 (North of Door) ---
                if (seg1Len > 0.1) {
                    const c1 = (seg1Start + seg1End) / 2;
                    if (isSolid) {
                        const solidWall = new THREE.Mesh(new THREE.BoxGeometry(thick, height, seg1Len), glassMat);
                        solidWall.position.set(x, height / 2, c1);
                        solidWall.castShadow = true;
                        solidWall.receiveShadow = true;
                        group.add(solidWall);

                        const kick1 = new THREE.Mesh(new THREE.BoxGeometry(thick + 0.02, 0.12, seg1Len), frameMat);
                        kick1.position.set(x, 0.06, c1);
                        group.add(kick1);

                        const topTrim1 = new THREE.Mesh(new THREE.BoxGeometry(thick + 0.02, 0.08, seg1Len), frameMat);
                        topTrim1.position.set(x, height - 0.04, c1);
                        group.add(topTrim1);
                    } else {
                        const g1 = new THREE.Mesh(new THREE.BoxGeometry(thick, height, seg1Len), glassMat);
                        g1.position.set(x, height / 2, c1);
                        group.add(g1);

                        const pb1 = new THREE.Mesh(new THREE.BoxGeometry(thick + 0.005, 0.5, seg1Len), privacyBandMat);
                        pb1.position.set(x, 1.3, c1);
                        group.add(pb1);

                        [1.05, 1.55].forEach(my => {
                            const mLine = new THREE.Mesh(new THREE.BoxGeometry(thick + 0.015, 0.025, seg1Len), frameMat);
                            mLine.position.set(x, my, c1);
                            group.add(mLine);
                        });

                        const kick1 = new THREE.Mesh(new THREE.BoxGeometry(thick + 0.02, 0.1, seg1Len), frameMat);
                        kick1.position.set(x, 0.05, c1);
                        group.add(kick1);
                    }

                    this.wallColliders.push({
                        minX: x - wallHalfThick,
                        maxX: x + wallHalfThick,
                        minZ: seg1Start,
                        maxZ: seg1End
                    });
                }

                // --- 2. SEGMENT 2 (South of Door) ---
                if (seg2Len > 0.1) {
                    const c2 = (seg2Start + seg2End) / 2;
                    if (isSolid) {
                        const solidWall = new THREE.Mesh(new THREE.BoxGeometry(thick, height, seg2Len), glassMat);
                        solidWall.position.set(x, height / 2, c2);
                        solidWall.castShadow = true;
                        solidWall.receiveShadow = true;
                        group.add(solidWall);

                        const kick2 = new THREE.Mesh(new THREE.BoxGeometry(thick + 0.02, 0.12, seg2Len), frameMat);
                        kick2.position.set(x, 0.06, c2);
                        group.add(kick2);

                        const topTrim2 = new THREE.Mesh(new THREE.BoxGeometry(thick + 0.02, 0.08, seg2Len), frameMat);
                        topTrim2.position.set(x, height - 0.04, c2);
                        group.add(topTrim2);
                    } else {
                        const g2 = new THREE.Mesh(new THREE.BoxGeometry(thick, height, seg2Len), glassMat);
                        g2.position.set(x, height / 2, c2);
                        group.add(g2);

                        const pb2 = new THREE.Mesh(new THREE.BoxGeometry(thick + 0.005, 0.5, seg2Len), privacyBandMat);
                        pb2.position.set(x, 1.3, c2);
                        group.add(pb2);

                        [1.05, 1.55].forEach(my => {
                            const mLine = new THREE.Mesh(new THREE.BoxGeometry(thick + 0.015, 0.025, seg2Len), frameMat);
                            mLine.position.set(x, my, c2);
                            group.add(mLine);
                        });

                        const kick2 = new THREE.Mesh(new THREE.BoxGeometry(thick + 0.02, 0.1, seg2Len), frameMat);
                        kick2.position.set(x, 0.05, c2);
                        group.add(kick2);
                    }

                    this.wallColliders.push({
                        minX: x - wallHalfThick,
                        maxX: x + wallHalfThick,
                        minZ: seg2Start,
                        maxZ: seg2End
                    });
                }

                // Door Lintel / Top Beam
                const totalLen = Math.abs(endCoord - startCoord);
                const topBar = new THREE.Mesh(new THREE.BoxGeometry(thick + 0.04, 0.12, totalLen), frameMat);
                topBar.position.set(x, height, (startCoord + endCoord) / 2);
                group.add(topBar);

                // Transom bar directly above door head
                const transomBar = new THREE.Mesh(new THREE.BoxGeometry(thick + 0.03, 0.08, doorWidth), frameMat);
                transomBar.position.set(x, doorH, doorCenter);
                group.add(transomBar);

                // If solid wall, close the space between door head (doorH) and ceiling (height) with solid panel
                if (isSolid) {
                    const transomH = height - doorH;
                    const solidTransom = new THREE.Mesh(new THREE.BoxGeometry(thick, transomH, doorWidth), glassMat);
                    solidTransom.position.set(x, doorH + transomH / 2, doorCenter);
                    solidTransom.castShadow = true;
                    solidTransom.receiveShadow = true;
                    group.add(solidTransom);
                }

                // Door posts
                [doorCenter - halfDoor, doorCenter + halfDoor].forEach(pz => {
                    const post = new THREE.Mesh(new THREE.BoxGeometry(thick + 0.04, height, 0.08), frameMat);
                    post.position.set(x, height / 2, pz);
                    group.add(post);
                });

                // Floor sill
                const sill = new THREE.Mesh(new THREE.BoxGeometry(0.18, 0.015, doorWidth), frameMat);
                sill.position.set(x, 0.008, doorCenter);
                group.add(sill);

                // --- 3. CONSTRUCT 3D INTERACTIVE DOOR(S) (along_z) ---
                const doorMaster = new THREE.Group();
                let leftPivot = null;
                let rightPivot = null;

                const makeDoorLeafZ = (leafWidth, hingeSide) => {
                    const leaf = new THREE.Group();
                    const leafThick = 0.05;

                    if (isSolid) {
                        const solidLeaf = new THREE.Mesh(new THREE.BoxGeometry(leafThick, doorH, leafWidth), doorFrameMat);
                        solidLeaf.position.set(0, 0, 0);
                        solidLeaf.castShadow = true;
                        leaf.add(solidLeaf);

                        const solidPanelMat = new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.65 });
                        const coreInlay = new THREE.Mesh(new THREE.BoxGeometry(leafThick + 0.005, doorH - 0.08, leafWidth - 0.06), solidPanelMat);
                        coreInlay.position.set(0, 0, 0);
                        leaf.add(coreInlay);

                        const plaqueMat = new THREE.MeshStandardMaterial({ color: 0x0f172a, roughness: 0.3 });
                        const plaque = new THREE.Mesh(new THREE.BoxGeometry(leafThick + 0.015, 0.20, 0.32), plaqueMat);
                        plaque.position.set(0, 1.55 - doorH / 2, 0);
                        leaf.add(plaque);

                        const dotMat = new THREE.MeshBasicMaterial({ color: 0x10b981 });
                        [-leafThick / 2 - 0.01, leafThick / 2 + 0.01].forEach(dx => {
                            const dot = new THREE.Mesh(new THREE.CircleGeometry(0.025, 16), dotMat);
                            dot.position.set(dx, 1.35 - doorH / 2, 0);
                            dot.rotation.y = dx < 0 ? -Math.PI / 2 : Math.PI / 2;
                            leaf.add(dot);
                        });

                        const kickMat = new THREE.MeshStandardMaterial({ color: 0xe2e8f0, metalness: 0.9, roughness: 0.15 });
                        const kickPlate = new THREE.Mesh(new THREE.BoxGeometry(leafThick + 0.01, 0.22, leafWidth - 0.02), kickMat);
                        kickPlate.position.set(0, 0.11 - doorH / 2, 0);
                        leaf.add(kickPlate);

                    } else {
                        const fTop = new THREE.Mesh(new THREE.BoxGeometry(leafThick, 0.06, leafWidth), doorFrameMat);
                        fTop.position.set(0, doorH / 2 - 0.03, 0);
                        leaf.add(fTop);

                        const fBottom = new THREE.Mesh(new THREE.BoxGeometry(leafThick, 0.12, leafWidth), doorFrameMat);
                        fBottom.position.set(0, -doorH / 2 + 0.06, 0);
                        leaf.add(fBottom);

                        const fNorth = new THREE.Mesh(new THREE.BoxGeometry(leafThick, doorH, 0.06), doorFrameMat);
                        fNorth.position.set(0, 0, -leafWidth / 2 + 0.03);
                        leaf.add(fNorth);

                        const fSouth = new THREE.Mesh(new THREE.BoxGeometry(leafThick, doorH, 0.06), doorFrameMat);
                        fSouth.position.set(0, 0, leafWidth / 2 - 0.03);
                        leaf.add(fSouth);

                        const gPane = new THREE.Mesh(new THREE.BoxGeometry(0.03, doorH - 0.16, leafWidth - 0.1), doorGlassMat);
                        leaf.add(gPane);

                        const dBand = new THREE.Mesh(new THREE.BoxGeometry(0.032, 0.5, leafWidth - 0.1), privacyBandMat);
                        dBand.position.set(0, 1.3 - doorH / 2, 0);
                        leaf.add(dBand);
                    }

                    // Pull handle hardware (vertical stainless steel bar on both sides)
                    const handleZ = hingeSide === 'north' ? (leafWidth / 2 - 0.12) : (-leafWidth / 2 + 0.12);
                    const handleY = 1.05 - doorH / 2;

                    [-0.045, 0.045].forEach(sideX => {
                        const bar = new THREE.Mesh(new THREE.CylinderGeometry(0.016, 0.016, 0.72, 16), handleMat);
                        bar.position.set(sideX, handleY, handleZ);
                        leaf.add(bar);

                        [-0.28, 0.28].forEach(by => {
                            const standoff = new THREE.Mesh(new THREE.CylinderGeometry(0.01, 0.01, 0.04, 12), handleMat);
                            standoff.rotation.z = Math.PI / 2;
                            standoff.position.set(sideX / 2, handleY + by, handleZ);
                            leaf.add(standoff);
                        });
                    });

                    return leaf;
                };

                if (isDouble) {
                    const leafW = (doorWidth - 0.1) / 2;

                    leftPivot = new THREE.Group();
                    leftPivot.position.set(x, 0, doorCenter - halfDoor + 0.04);
                    const northLeaf = makeDoorLeafZ(leafW, 'north');
                    northLeaf.position.set(0, doorH / 2, leafW / 2);
                    leftPivot.add(northLeaf);
                    doorMaster.add(leftPivot);

                    rightPivot = new THREE.Group();
                    rightPivot.position.set(x, 0, doorCenter + halfDoor - 0.04);
                    const southLeaf = makeDoorLeafZ(leafW, 'south');
                    southLeaf.position.set(0, doorH / 2, -leafW / 2);
                    rightPivot.add(southLeaf);
                    doorMaster.add(rightPivot);

                } else {
                    const leafW = doorWidth - 0.08;

                    leftPivot = new THREE.Group();
                    leftPivot.position.set(x, 0, doorCenter - halfDoor + 0.04);
                    const singleLeaf = makeDoorLeafZ(leafW, 'north');
                    singleLeaf.position.set(0, doorH / 2, leafW / 2);
                    leftPivot.add(singleLeaf);
                    doorMaster.add(leftPivot);
                }

                group.add(doorMaster);

                const doorCollider = {
                    minX: x - wallHalfThick,
                    maxX: x + wallHalfThick,
                    minZ: doorCenter - halfDoor + 0.05,
                    maxZ: doorCenter + halfDoor - 0.05
                };

                if (!doorId) doorId = `door_${this.doors.size + 1}`;

                doorMaster.traverse((child) => {
                    if (child.isMesh) {
                        child.userData = { doorId: doorId, isDoor: true };
                        this.interactiveObjects.push(child);
                    }
                });

                this.doors.set(doorId, {
                    id: doorId,
                    type: isDouble ? 'double' : 'single',
                    orientation: 'along_z',
                    center: new THREE.Vector3(x, 0, doorCenter),
                    fixedCoord: x,
                    width: doorWidth,
                    height: doorH,
                    leftPivot: leftPivot,
                    rightPivot: rightPivot,
                    currentAngle: 0,
                    targetAngle: 0,
                    maxAngle: isDouble ? 1.45 : 1.40,
                    state: 'closed',
                    collider: doorCollider,
                    manualLockUntil: 0,
                    masterGroup: doorMaster
                });
            }

            parent.add(group);
        }

        createSolidPartition(parent, fixedCoord, startCoord, endCoord, height, frameMat, glassMat, orientation = 'along_x', isSolid = false) {
            const group = new THREE.Group();
            const thick = 0.08;
            const sStart = Math.min(startCoord, endCoord);
            const sEnd = Math.max(startCoord, endCoord);
            const len = Math.max(0.1, sEnd - sStart);
            const center = (sStart + sEnd) / 2;
            const wallHalfThick = 0.22;

            if (orientation === 'along_x') {
                const z = fixedCoord;
                const pane = new THREE.Mesh(new THREE.BoxGeometry(len, height, thick), glassMat);
                pane.position.set(center, height / 2, z);
                pane.castShadow = true;
                pane.receiveShadow = true;
                group.add(pane);

                if (!isSolid) {
                    const privacyBandMat = new THREE.MeshPhysicalMaterial({
                        color: glassMat.color ? glassMat.color.getHex() : 0xe2e8f0,
                        transparent: true,
                        opacity: 0.92,
                        roughness: 0.72,
                        transmission: 0.60,
                        thickness: 1.5,
                        ior: 1.48,
                        depthWrite: false,
                        side: THREE.DoubleSide
                    });
                    const pb = new THREE.Mesh(new THREE.BoxGeometry(len, 0.5, thick + 0.005), privacyBandMat);
                    pb.position.set(center, 1.3, z);
                    group.add(pb);

                    [1.05, 1.55].forEach(my => {
                        const mLine = new THREE.Mesh(new THREE.BoxGeometry(len, 0.025, thick + 0.015), frameMat);
                        mLine.position.set(center, my, z);
                        group.add(mLine);
                    });
                }

                const kick = new THREE.Mesh(new THREE.BoxGeometry(len, 0.12, thick + 0.02), frameMat);
                kick.position.set(center, 0.06, z);
                group.add(kick);

                const topBar = new THREE.Mesh(new THREE.BoxGeometry(len, 0.12, thick + 0.04), frameMat);
                topBar.position.set(center, height, z);
                group.add(topBar);

                this.wallColliders.push({
                    minX: sStart,
                    maxX: sEnd,
                    minZ: z - wallHalfThick,
                    maxZ: z + wallHalfThick
                });
            } else {
                const x = fixedCoord;
                const pane = new THREE.Mesh(new THREE.BoxGeometry(thick, height, len), glassMat);
                pane.position.set(x, height / 2, center);
                pane.castShadow = true;
                pane.receiveShadow = true;
                group.add(pane);

                if (!isSolid) {
                    const privacyBandMat = new THREE.MeshPhysicalMaterial({
                        color: glassMat.color ? glassMat.color.getHex() : 0xe2e8f0,
                        transparent: true,
                        opacity: 0.92,
                        roughness: 0.72,
                        transmission: 0.60,
                        thickness: 1.5,
                        ior: 1.48,
                        depthWrite: false,
                        side: THREE.DoubleSide
                    });
                    const pb = new THREE.Mesh(new THREE.BoxGeometry(thick + 0.005, 0.5, len), privacyBandMat);
                    pb.position.set(x, 1.3, center);
                    group.add(pb);

                    [1.05, 1.55].forEach(my => {
                        const mLine = new THREE.Mesh(new THREE.BoxGeometry(thick + 0.015, 0.025, len), frameMat);
                        mLine.position.set(x, my, center);
                        group.add(mLine);
                    });
                }

                const kick = new THREE.Mesh(new THREE.BoxGeometry(thick + 0.02, 0.12, len), frameMat);
                kick.position.set(x, 0.06, center);
                group.add(kick);

                const topBar = new THREE.Mesh(new THREE.BoxGeometry(thick + 0.04, 0.12, len), frameMat);
                topBar.position.set(x, height, center);
                group.add(topBar);

                this.wallColliders.push({
                    minX: x - wallHalfThick,
                    maxX: x + wallHalfThick,
                    minZ: sStart,
                    maxZ: sEnd
                });
            }

            parent.add(group);
            return group;
        }

        createPartitionWall(parent, x, z, width, depth, height, frameMat, glassMat, doorStyle = 'none') {
            const group = new THREE.Group();
            group.position.set(x, 0, z);

            const glass = new THREE.Mesh(new THREE.BoxGeometry(width, height, depth), glassMat);
            glass.position.set(0, height / 2, 0);
            group.add(glass);

            const topBar = new THREE.Mesh(new THREE.BoxGeometry(width + 0.1, 0.1, depth + 0.1), frameMat);
            topBar.position.set(0, height, 0);
            group.add(topBar);

            this.wallColliders.push({
                minX: x - width / 2 - 0.15,
                maxX: x + width / 2 + 0.15,
                minZ: z - depth / 2 - 0.15,
                maxZ: z + depth / 2 + 0.15
            });

            parent.add(group);
        }

        createOverheadWingSign(parent, x, y, z, title, subtitle, glowColor) {
            const canvas = document.createElement('canvas');
            canvas.width = 580;
            canvas.height = 120;
            const ctx = canvas.getContext('2d');

            ctx.fillStyle = 'rgba(15, 23, 42, 0.9)';
            this.roundRect(ctx, 10, 10, 560, 100, 20);
            ctx.fill();

            ctx.strokeStyle = this.hexToRgba(glowColor, 0.7);
            ctx.lineWidth = 3.5;
            ctx.stroke();

            ctx.fillStyle = '#ffffff';
            ctx.font = 'bold 32px Inter, sans-serif';
            ctx.fillText(title, 28, 52);

            ctx.fillStyle = '#94a3b8';
            ctx.font = '500 20px Inter, sans-serif';
            ctx.fillText(subtitle, 28, 88);

            const texture = new THREE.CanvasTexture(canvas);
            const sprite = new THREE.Sprite(new THREE.SpriteMaterial({ map: texture, transparent: true }));
            sprite.position.set(x, y, z);
            sprite.scale.set(5.8, 1.2, 1);
            parent.add(sprite);
        }

        buildParkedCar(parent, x, z, paintColor) {
            const car = new THREE.Group();
            car.position.set(x, 0, z);

            const carPaintMat = new THREE.MeshStandardMaterial({
                color: paintColor,
                metalness: 0.85,
                roughness: 0.2
            });
            const glassMat = new THREE.MeshPhysicalMaterial({ color: 0x38bdf8, transparent: true, opacity: 0.6, roughness: 0.1 });
            const tireMat = new THREE.MeshStandardMaterial({ color: 0x18181b, roughness: 0.9 });

            // Chassis
            const chassis = new THREE.Mesh(new THREE.BoxGeometry(2.1, 0.6, 4.4), carPaintMat);
            chassis.position.set(0, 0.5, 0);
            chassis.castShadow = true;
            car.add(chassis);

            // Cabin
            const cabin = new THREE.Mesh(new THREE.BoxGeometry(1.8, 0.55, 2.4), carPaintMat);
            cabin.position.set(0, 1.05, -0.2);
            car.add(cabin);

            // Wheels
            [[-1.0, -1.4], [1.0, -1.4], [-1.0, 1.4], [1.0, 1.4]].forEach(wp => {
                const wheel = new THREE.Mesh(new THREE.CylinderGeometry(0.35, 0.35, 0.25, 16), tireMat);
                wheel.rotation.z = Math.PI / 2;
                wheel.position.set(wp[0], 0.35, wp[1]);
                wheel.castShadow = true;
                car.add(wheel);
            });

            parent.add(car);
        }

        // ==========================================
        // 2. ALL CAMPUS FACILITIES (100% 2D BLUEPRINT SVG LAYOUT)
        // ==========================================
        buildAllCampusFacilities() {
            const facGroup = new THREE.Group();

            // 1. CENTRAL WAITING ROTUNDA (Center Atrium: x = 0, z = 0.5)
            this.buildCentralRotundaFacility(facGroup, 0, 0.5);

            // 2. GRAND ENTRANCE RECEPTION DESK & DIRECTORY KIOSK (x = 0, z = 7.2 & 9.8)
            this.buildEntranceAndReception(facGroup, 0, 7.2);

            // 3. EXECUTIVE BOARDROOM LOUNGE (Center-North: x = 0, z = -7.5)
            this.buildExecutiveBoardroomFacility(facGroup, 0, -7.5);

            // 4. WEST WING: MARKETING COLLABORATION (x = -16.0, z = -8.5)
            this.buildMarketingCollaborationFacility(facGroup, -16.0, -8.5);

            // 5. WEST WING: SALES WORKBENCH (x = -16.0, z = 0.0)
            this.buildSalesCollaborationFacility(facGroup, -16.0, 0.0);

            // 6. WEST WING: MEETING ROOM CONFERENCE TABLE (x = -16.0, z = 6.5)
            this.buildMeetingRoomFacility(facGroup, -16.0, 6.5);

            // 7. EAST WING: OPERATIONS COLLABORATION (x = 12.6, z = -8.5)
            this.buildOperationsCollaborationFacility(facGroup, 12.6, -8.5);

            // 7B. EAST WING: HR & PEOPLE WORKSTATION SIGN (Near Server Room: x = 16.0, z = -7.5)
            this.createNeonSignText(facGroup, 16.0, 3.4, -7.5, '👥 HR & People Desk', 0xf97316, 3.0);

            // 8. EAST WING: SERVER ROOM & IT SUPPORT (x = 21.375, z = -10.375)
            this.buildServerRoomFacility(facGroup, 21.375, -10.375);

            // 9. EAST WING: DOCUMENT ARCHIVE (x = 21.375, z = -1.8)
            this.buildDocumentRoomFacility(facGroup, 21.375, -1.8);

            // 10A. EAST WING: BISTRO LOUNGE & CAFE ENTRY (x = 11.35, z = 4.15)
            this.buildBistroLoungeFacility(facGroup, 11.35, 4.15);

            // 10B. EAST WING: PANTRY & DINING KITCHENETTE (x = 20.125, z = 4.15)
            this.buildPantryFacility(facGroup, 20.125, 4.15);

            // 10C. Dedicated Pantry Atrium Entrance Sign (x = 7.2, z = 4.0)
            this.createNeonSignText(facGroup, 7.2, 3.5, 4.0, '☕ Pantry & Bistro Lounge', 0x10b981, 3.2);

            // 11. EAST WING: RESTROOM (x = 16.0, z = 8.8, spanning x = 7.2 to 24.75, z = 6.8 to 10.75)
            this.buildRestroomFacility(facGroup, 16.0, 8.8);

            // 12. Landscaped Potted Trees & Planters (strictly matching 2D interior nodes)
            this.buildPottedPlant(facGroup, -5.2, -3.2, 'ficus');
            this.buildPottedPlant(facGroup, 5.2, -3.2, 'monstera');
            this.buildPottedPlant(facGroup, -5.2, 4.2, 'monstera');
            this.buildPottedPlant(facGroup, 5.2, 4.2, 'ficus');
            this.buildPottedPlant(facGroup, -24.0, 9.8, 'ficus');
            this.buildPottedPlant(facGroup, -8.0, 9.8, 'ficus');
            this.buildPottedPlant(facGroup, 8.0, 9.8, 'ficus');
            this.buildPottedPlant(facGroup, 24.0, 9.8, 'ficus');

            this.scene.add(facGroup);
        }

        buildCentralRotundaFacility(parent, x, z) {
            const rotunda = new THREE.Group();
            rotunda.position.set(x, 0, z);

            // 1. Circular Raised Planter Bed in Middle (radius 1.25)
            const planterMat = new THREE.MeshStandardMaterial({ color: 0x0f172a, roughness: 0.3 });
            const soilMat = new THREE.MeshStandardMaterial({ color: 0x271e18, roughness: 0.9 });
            const leafMat = new THREE.MeshStandardMaterial({ color: 0x15803d, roughness: 0.4 });

            const planterRim = new THREE.Mesh(new THREE.CylinderGeometry(1.35, 1.35, 0.45, 32), planterMat);
            planterRim.position.y = 0.225;
            rotunda.add(planterRim);

            const soil = new THREE.Mesh(new THREE.CylinderGeometry(1.28, 1.28, 0.1, 32), soilMat);
            soil.position.y = 0.42;
            rotunda.add(soil);

            // Center Tree in Planter
            const trunk = new THREE.Mesh(new THREE.CylinderGeometry(0.12, 0.16, 2.4), new THREE.MeshStandardMaterial({ color: 0x5c3d2e }));
            trunk.position.y = 1.6;
            rotunda.add(trunk);

            for (let i = 0; i < 6; i++) {
                const crown = new THREE.Mesh(new THREE.DodecahedronGeometry(0.95 - i * 0.1, 1), leafMat);
                crown.position.set((Math.random() - 0.5) * 0.4, 2.4 + i * 0.4, (Math.random() - 0.5) * 0.4);
                rotunda.add(crown);
            }

            // 2. Circular Sectional Banquette Sofa around the planter (inner r: 1.6, outer r: 2.7)
            const sofaMat = new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.6 });
            const sofaSeat = new THREE.Mesh(new THREE.CylinderGeometry(2.7, 2.7, 0.4, 32), sofaMat);
            sofaSeat.position.y = 0.25;
            rotunda.add(sofaSeat);

            // 8 Distinct Individual Cushions on the Circular Banquette (Matching rotunda_0 to rotunda_7)
            const cushionMat = new THREE.MeshStandardMaterial({ color: 0x334155, roughness: 0.55 });
            for (let k = 0; k < 8; k++) {
                const angle = k * (Math.PI / 4);
                const cx = Math.cos(angle) * 2.15;
                const cz = Math.sin(angle) * 2.15;
                const cushion = new THREE.Mesh(new THREE.BoxGeometry(0.72, 0.08, 0.72), cushionMat);
                cushion.position.set(cx, 0.46, cz);
                cushion.rotation.y = -angle;
                cushion.castShadow = true;
                rotunda.add(cushion);
            }

            // 3. Left Lounge Modular Charcoal Sofa & Table (x = -5.8)
            const leftLounge = new THREE.Group();
            leftLounge.position.set(-5.8, 0, 0);
            const sofaL = new THREE.Mesh(new THREE.BoxGeometry(0.85, 0.65, 2.8), sofaMat);
            sofaL.position.set(0, 0.45, 0);
            leftLounge.add(sofaL);
            const coffeeL = new THREE.Mesh(new THREE.BoxGeometry(0.6, 0.4, 1.6), new THREE.MeshPhysicalMaterial({ color: 0x38bdf8, transparent: true, opacity: 0.4, roughness: 0.1 }));
            coffeeL.position.set(1.1, 0.25, 0);
            leftLounge.add(coffeeL);
            rotunda.add(leftLounge);

            // 4. Right Lounge Modular Charcoal Sofa & Table (x = 5.8)
            const rightLounge = new THREE.Group();
            rightLounge.position.set(5.8, 0, 0);
            const sofaR = new THREE.Mesh(new THREE.BoxGeometry(0.85, 0.65, 2.8), sofaMat);
            sofaR.position.set(0, 0.45, 0);
            rightLounge.add(sofaR);
            const coffeeR = new THREE.Mesh(new THREE.BoxGeometry(0.6, 0.4, 1.6), new THREE.MeshPhysicalMaterial({ color: 0x38bdf8, transparent: true, opacity: 0.4, roughness: 0.1 }));
            coffeeR.position.set(-1.1, 0.25, 0);
            rightLounge.add(coffeeR);
            rotunda.add(rightLounge);

            parent.add(rotunda);
        }

        buildEntranceAndReception(parent, x, z) {
            const entrance = new THREE.Group();
            entrance.position.set(x, 0, z);

            // 1. Curved Reception Counter (Facing South towards entrance doors)
            const deskMat = new THREE.MeshStandardMaterial({ color: 0x0f1d33, roughness: 0.2 });
            const deskTopMat = new THREE.MeshStandardMaterial({ color: 0xffffff, roughness: 0.1 });

            const deskBase = new THREE.Mesh(new THREE.BoxGeometry(4.8, 1.15, 1.2), deskMat);
            deskBase.position.set(0, 0.58, 0);
            deskBase.castShadow = true;
            entrance.add(deskBase);

            const deskTop = new THREE.Mesh(new THREE.BoxGeometry(5.0, 0.08, 1.3), deskTopMat);
            deskTop.position.set(0, 1.2, 0);
            entrance.add(deskTop);

            // Front Illuminated Text on Desk: "COOCA AI Virtual Office"
            this.createNeonSignText(entrance, 0, 0.65, 0.65, 'COOCA AI Virtual Office', 0x38bdf8, 3.4);

            // 2 Receptionist Chairs Behind Desk (at z = -0.7 relative)
            [-1.1, 1.1].forEach(cx => {
                const chair = new THREE.Mesh(new THREE.BoxGeometry(0.55, 0.85, 0.55), new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.5 }));
                chair.position.set(cx, 0.55, -0.7);
                entrance.add(chair);
            });

            // Dual Reception Displays
            [-1.1, 1.1].forEach(mx => {
                const mon = new THREE.Mesh(new THREE.BoxGeometry(0.75, 0.5, 0.04), new THREE.MeshStandardMaterial({ color: 0x0f172a, emissive: 0x38bdf8, emissiveIntensity: 0.7 }));
                mon.position.set(mx, 1.6, -0.15);
                entrance.add(mon);
            });

            // 2. Main Entrance Floor Mat (at relative z = 2.6 -> absolute z = 9.8)
            const mat = new THREE.Mesh(new THREE.BoxGeometry(3.6, 0.02, 1.4), new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.9 }));
            mat.position.set(0, 0.015, 2.6);
            entrance.add(mat);

            // 3. Automatic Double Sliding Glass Doors (at relative z = 3.6 -> absolute z = 10.8)
            const doorGlassMat = new THREE.MeshPhysicalMaterial({ color: 0x38bdf8, transparent: true, opacity: 0.5, roughness: 0.1, transmission: 0.9 });
            const doorLeft = new THREE.Mesh(new THREE.BoxGeometry(1.6, 3.2, 0.06), doorGlassMat);
            doorLeft.position.set(-0.9, 1.6, 3.6);
            entrance.add(doorLeft);

            const doorRight = new THREE.Mesh(new THREE.BoxGeometry(1.6, 3.2, 0.06), doorGlassMat);
            doorRight.position.set(0.9, 1.6, 3.6);
            entrance.add(doorRight);

            parent.add(entrance);
        }

        buildExecutiveBoardroomFacility(parent, x, z) {
            const boardroom = new THREE.Group();
            boardroom.position.set(x, 0, z);

            // 1. Executive Conversation Lounge: 2 Emerald/Teal Sofas Facing Each Other (matching 2D SVG)
            const tealSofaMat = new THREE.MeshStandardMaterial({ color: 0x0f766e, roughness: 0.4 });
            const seatCushionMat = new THREE.MeshStandardMaterial({ color: 0x115e59, roughness: 0.5 });
            const glassTableMat = new THREE.MeshPhysicalMaterial({ color: 0x38bdf8, transparent: true, opacity: 0.5, roughness: 0.1 });

            // North Sofa (3-Seater: relative z = -1.3 -> absolute z = -8.8)
            const northSofa = new THREE.Mesh(new THREE.BoxGeometry(4.2, 0.7, 0.9), tealSofaMat);
            northSofa.position.set(0, 0.45, -1.3);
            northSofa.castShadow = true;
            boardroom.add(northSofa);

            const northSofaBack = new THREE.Mesh(new THREE.BoxGeometry(4.2, 0.8, 0.25), tealSofaMat);
            northSofaBack.position.set(0, 0.9, -1.65);
            boardroom.add(northSofaBack);

            // 3 Individual Cushions on North Sofa (boardroom_n1 to n3)
            [-1.2, 0.0, 1.2].forEach(cx => {
                const c = new THREE.Mesh(new THREE.BoxGeometry(1.15, 0.1, 0.75), seatCushionMat);
                c.position.set(cx, 0.82, -1.3);
                boardroom.add(c);
            });

            // South Sofa (3-Seater: relative z = 1.3 -> absolute z = -6.2)
            const southSofa = new THREE.Mesh(new THREE.BoxGeometry(4.2, 0.7, 0.9), tealSofaMat);
            southSofa.position.set(0, 0.45, 1.3);
            southSofa.castShadow = true;
            boardroom.add(southSofa);

            const southSofaBack = new THREE.Mesh(new THREE.BoxGeometry(4.2, 0.8, 0.25), tealSofaMat);
            southSofaBack.position.set(0, 0.9, 1.65);
            boardroom.add(southSofaBack);

            // 3 Individual Cushions on South Sofa (boardroom_s1 to s3)
            [-1.2, 0.0, 1.2].forEach(cx => {
                const c = new THREE.Mesh(new THREE.BoxGeometry(1.15, 0.1, 0.75), seatCushionMat);
                c.position.set(cx, 0.82, 1.3);
                boardroom.add(c);
            });

            // Center Glass Coffee Table (relative z = 0 -> absolute z = -7.5)
            const coffeeTable = new THREE.Mesh(new THREE.BoxGeometry(3.2, 0.08, 1.4), glassTableMat);
            coffeeTable.position.set(0, 0.5, 0);
            coffeeTable.castShadow = true;
            boardroom.add(coffeeTable);

            // Chrome Table Legs
            const chromeLegMat = new THREE.MeshStandardMaterial({ color: 0xe2e8f0, metalness: 0.9, roughness: 0.1 });
            [[-1.4, -0.6], [1.4, -0.6], [-1.4, 0.6], [1.4, 0.6]].forEach(p => {
                const leg = new THREE.Mesh(new THREE.CylinderGeometry(0.04, 0.04, 0.5), chromeLegMat);
                leg.position.set(p[0], 0.25, p[1]);
                boardroom.add(leg);
            });

            // 2 Teal Armchairs on West & East (boardroom_w1 & e1: relative x = -2.5 and 2.5)
            [-2.5, 2.5].forEach(ax => {
                const armChair = new THREE.Mesh(new THREE.BoxGeometry(0.85, 0.7, 0.85), tealSofaMat);
                armChair.position.set(ax, 0.45, 0);
                boardroom.add(armChair);

                const armCushion = new THREE.Mesh(new THREE.BoxGeometry(0.72, 0.1, 0.72), seatCushionMat);
                armCushion.position.set(ax, 0.82, 0);
                boardroom.add(armCushion);
            });

            // Wall Display Monitor: "COOCA BOARDROOM // Executive Audit Q4 Strategy"
            const screen = new THREE.Mesh(new THREE.BoxGeometry(4.2, 2.0, 0.05), new THREE.MeshStandardMaterial({ color: 0x0f172a, emissive: 0x38bdf8, emissiveIntensity: 0.75 }));
            screen.position.set(0, 3.4, -3.2);
            boardroom.add(screen);

            this.createNeonSignText(boardroom, 0, 4.6, -3.0, 'COOCA BOARDROOM | Executive Audit', 0x38bdf8, 4.8);

            parent.add(boardroom);
        }

        buildMarketingCollaborationFacility(parent, x, z) {
            const mkt = new THREE.Group();
            mkt.position.set(x, 0, z);

            // 8-Seater Collaboration Table Top (matching 2D SVG translate(56, 164))
            const tableMat = new THREE.MeshStandardMaterial({ color: 0x453120, roughness: 0.4 });
            const table = new THREE.Mesh(new THREE.BoxGeometry(5.2, 0.05, 1.5), tableMat);
            table.position.set(0, 0.74, 0);
            table.castShadow = true;
            mkt.add(table);

            // Table legs (height 0.70m, center 0.35m)
            const legMat = new THREE.MeshStandardMaterial({ color: 0x18181b, metalness: 0.8, roughness: 0.2 });
            [[-2.3, -0.55], [2.3, -0.55], [-2.3, 0.55], [2.3, 0.55]].forEach(p => {
                const leg = new THREE.Mesh(new THREE.CylinderGeometry(0.035, 0.03, 0.70, 16), legMat);
                leg.position.set(p[0], 0.35, p[1]);
                mkt.add(leg);
            });

            // 4 Chairs North & 4 Chairs South (Matching mkt_collab_n1..n4 and s1..s4, seat at 0.46m)
            const chairMat = new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.5 });
            [-1.8, -0.6, 0.6, 1.8].forEach(cx => {
                // North chair (relative z = -0.85)
                const cn = new THREE.Mesh(new THREE.BoxGeometry(0.48, 0.06, 0.48), chairMat);
                cn.position.set(cx, 0.46, -0.85);
                mkt.add(cn);

                const cnBack = new THREE.Mesh(new THREE.BoxGeometry(0.46, 0.42, 0.04), chairMat);
                cnBack.position.set(cx, 0.68, -1.07);
                mkt.add(cnBack);

                // South chair (relative z = 0.85)
                const cs = new THREE.Mesh(new THREE.BoxGeometry(0.48, 0.06, 0.48), chairMat);
                cs.position.set(cx, 0.46, 0.85);
                mkt.add(cs);

                const csBack = new THREE.Mesh(new THREE.BoxGeometry(0.46, 0.42, 0.04), chairMat);
                csBack.position.set(cx, 0.68, 1.07);
                mkt.add(csBack);
            });

            // Center Planter & Open Laptops on table
            const plant = new THREE.Mesh(new THREE.BoxGeometry(1.2, 0.12, 0.35), new THREE.MeshStandardMaterial({ color: 0x15803d }));
            plant.position.set(0, 0.82, 0);
            mkt.add(plant);

            [-1.5, 1.5].forEach(lx => {
                const laptop = new THREE.Mesh(new THREE.BoxGeometry(0.38, 0.015, 0.26), new THREE.MeshStandardMaterial({ color: 0x94a3b8 }));
                laptop.position.set(lx, 0.77, 0.2);
                mkt.add(laptop);
            });

            this.createNeonSignText(mkt, 0, 3.6, 0, 'Growth Lab | Marketing Collab', 0xa855f7, 3.4);
            parent.add(mkt);
        }

        buildSalesCollaborationFacility(parent, x, z) {
            const sales = new THREE.Group();
            sales.position.set(x, 0, z);

            // 8-Seater Long Sales Workbench (matching 2D SVG translate(56, 386))
            const tableMat = new THREE.MeshStandardMaterial({ color: 0x3d2716, roughness: 0.4 });
            const table = new THREE.Mesh(new THREE.BoxGeometry(5.2, 0.05, 1.4), tableMat);
            table.position.set(0, 0.74, 0);
            table.castShadow = true;
            sales.add(table);

            const legMat = new THREE.MeshStandardMaterial({ color: 0x18181b, metalness: 0.8, roughness: 0.2 });
            [[-2.3, -0.5], [2.3, -0.5], [-2.3, 0.5], [2.3, 0.5]].forEach(p => {
                const leg = new THREE.Mesh(new THREE.CylinderGeometry(0.035, 0.03, 0.70, 16), legMat);
                leg.position.set(p[0], 0.35, p[1]);
                sales.add(leg);
            });

            // 4 Chairs North & 4 Chairs South (Matching sales_wb_n1..n4 and s1..s4, seat at 0.46m)
            const chairMat = new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.5 });
            [-1.8, -0.6, 0.6, 1.8].forEach(cx => {
                const cn = new THREE.Mesh(new THREE.BoxGeometry(0.48, 0.06, 0.48), chairMat);
                cn.position.set(cx, 0.46, -0.85);
                sales.add(cn);

                const cnBack = new THREE.Mesh(new THREE.BoxGeometry(0.46, 0.42, 0.04), chairMat);
                cnBack.position.set(cx, 0.68, -1.07);
                sales.add(cnBack);

                const cs = new THREE.Mesh(new THREE.BoxGeometry(0.48, 0.06, 0.48), chairMat);
                cs.position.set(cx, 0.46, 0.85);
                sales.add(cs);

                const csBack = new THREE.Mesh(new THREE.BoxGeometry(0.46, 0.42, 0.04), chairMat);
                csBack.position.set(cx, 0.68, 1.07);
                sales.add(csBack);
            });

            this.createNeonSignText(sales, 0, 3.6, 0, 'Sales & Revenue Workbench', 0x10b981, 3.4);
            parent.add(sales);
        }

        buildMeetingRoomFacility(parent, x, z) {
            const meeting = new THREE.Group();
            meeting.position.set(x, 0, z);

            // Large 8-Seater Executive Conference Table (matching 2D SVG translate(90, 535))
            const tableMat = new THREE.MeshStandardMaterial({ color: 0x22140a, roughness: 0.3 });
            const table = new THREE.Mesh(new THREE.BoxGeometry(4.6, 0.05, 1.8), tableMat);
            table.position.set(0, 0.74, 0);
            table.castShadow = true;
            meeting.add(table);

            const legMat = new THREE.MeshStandardMaterial({ color: 0x18181b, metalness: 0.8, roughness: 0.2 });
            [[-2.0, -0.6], [2.0, -0.6], [-2.0, 0.6], [2.0, 0.6]].forEach(p => {
                const leg = new THREE.Mesh(new THREE.CylinderGeometry(0.04, 0.035, 0.70, 16), legMat);
                leg.position.set(p[0], 0.35, p[1]);
                meeting.add(leg);
            });

            // 3 Chairs North (relative z = -1.1), 3 Chairs South (relative z = 1.1)
            const chairMat = new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.5 });
            [-1.5, 0, 1.5].forEach(cx => {
                const cn = new THREE.Mesh(new THREE.BoxGeometry(0.50, 0.06, 0.50), chairMat);
                cn.position.set(cx, 0.46, -1.05);
                meeting.add(cn);

                const cnBack = new THREE.Mesh(new THREE.BoxGeometry(0.48, 0.44, 0.05), chairMat);
                cnBack.position.set(cx, 0.70, -1.28);
                meeting.add(cnBack);

                const cs = new THREE.Mesh(new THREE.BoxGeometry(0.50, 0.06, 0.50), chairMat);
                cs.position.set(cx, 0.46, 1.05);
                meeting.add(cs);

                const csBack = new THREE.Mesh(new THREE.BoxGeometry(0.48, 0.44, 0.05), chairMat);
                csBack.position.set(cx, 0.70, 1.28);
                meeting.add(csBack);
            });

            // 1 Chair West (relative x = -2.5), 1 Chair East (relative x = 2.5)
            [-2.5, 2.5].forEach(cx => {
                const c = new THREE.Mesh(new THREE.BoxGeometry(0.50, 0.06, 0.50), chairMat);
                c.position.set(cx, 0.46, 0);
                meeting.add(c);

                const cBack = new THREE.Mesh(new THREE.BoxGeometry(0.05, 0.44, 0.48), chairMat);
                cBack.position.set(cx < 0 ? cx - 0.23 : cx + 0.23, 0.70, 0);
                meeting.add(cBack);
            });

            // Large Presentation TV on North Wall (relative z = -3.85 -> absolute z = 2.65)
            const tvScreen = new THREE.Mesh(new THREE.BoxGeometry(3.8, 1.8, 0.05), new THREE.MeshStandardMaterial({ color: 0x0284c7, emissive: 0x38bdf8, emissiveIntensity: 0.7 }));
            tvScreen.position.set(0, 2.6, -3.85);
            meeting.add(tvScreen);

            // Left Lounge Sofa Bench along West Wall (relative x = -8.0 -> absolute x = -24.0)
            const bench = new THREE.Mesh(new THREE.BoxGeometry(0.7, 0.5, 3.8), new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.6 }));
            bench.position.set(-8.0, 0.25, 0);
            meeting.add(bench);

            this.createNeonSignText(meeting, 0, 3.8, 0, 'Executive Conference', 0x38bdf8, 4.2);
            parent.add(meeting);
        }

        buildOperationsCollaborationFacility(parent, x, z) {
            const ops = new THREE.Group();
            ops.position.set(x, 0, z);

            // 8-Seater Operations Collaboration Workbench (matching 2D SVG translate(778, 164))
            const tableMat = new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.4 });
            const table = new THREE.Mesh(new THREE.BoxGeometry(5.2, 0.05, 1.5), tableMat);
            table.position.set(0, 0.74, 0);
            table.castShadow = true;
            ops.add(table);

            const legMat = new THREE.MeshStandardMaterial({ color: 0x18181b, metalness: 0.8, roughness: 0.2 });
            [[-2.3, -0.55], [2.3, -0.55], [-2.3, 0.55], [2.3, 0.55]].forEach(p => {
                const leg = new THREE.Mesh(new THREE.CylinderGeometry(0.035, 0.03, 0.70, 16), legMat);
                leg.position.set(p[0], 0.35, p[1]);
                ops.add(leg);
            });

            // 4 Chairs North & 4 Chairs South (seat at 0.46m)
            const chairMat = new THREE.MeshStandardMaterial({ color: 0x0f172a, roughness: 0.5 });
            [-1.8, -0.6, 0.6, 1.8].forEach(cx => {
                const cn = new THREE.Mesh(new THREE.BoxGeometry(0.48, 0.06, 0.48), chairMat);
                cn.position.set(cx, 0.46, -0.85);
                ops.add(cn);

                const cnBack = new THREE.Mesh(new THREE.BoxGeometry(0.46, 0.42, 0.04), chairMat);
                cnBack.position.set(cx, 0.68, -1.07);
                ops.add(cnBack);

                const cs = new THREE.Mesh(new THREE.BoxGeometry(0.48, 0.06, 0.48), chairMat);
                cs.position.set(cx, 0.46, 0.85);
                ops.add(cs);

                const csBack = new THREE.Mesh(new THREE.BoxGeometry(0.46, 0.42, 0.04), chairMat);
                csBack.position.set(cx, 0.68, 1.07);
                ops.add(csBack);
            });

            this.createNeonSignText(ops, 0, 3.6, 0, 'Operations & Logistics Workbench', 0x10b981, 3.6);
            parent.add(ops);
        }

        buildServerRoomFacility(parent, x, z) {
            const itHub = new THREE.Group();
            itHub.position.set(x, 0, z);

            // 3 Tall Server Rack Cabinets along North Wall (relative z = -4.4 -> absolute z = -14.8)
            const rackMat = new THREE.MeshStandardMaterial({ color: 0x111827, roughness: 0.8 });
            [-1.875, 0, 1.875].forEach(rx => {
                const rack = new THREE.Mesh(new THREE.BoxGeometry(1.1, 2.9, 0.8), rackMat);
                rack.position.set(rx, 1.45, -4.4);
                rack.castShadow = true;
                itHub.add(rack);

                // LED rows
                for (let row = 0; row < 6; row++) {
                    const ledColor = row % 3 === 0 ? 0x10b981 : (row % 3 === 1 ? 0x38bdf8 : 0xf59e0b);
                    const led = new THREE.Mesh(new THREE.BoxGeometry(0.8, 0.08, 0.05), new THREE.MeshBasicMaterial({ color: ledColor }));
                    led.position.set(rx, 0.5 + row * 0.4, -3.98);
                    itHub.add(led);
                    this.blinkingLeds.push(led);
                }
            });

            // IT Support Workstation Desk & Chair (relative z = 1.875 -> absolute z = -8.5)
            const itDesk = new THREE.Mesh(new THREE.BoxGeometry(2.0, 0.04, 0.95), new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.3 }));
            itDesk.position.set(0, 0.74, 1.875);
            itDesk.castShadow = true;
            itHub.add(itDesk);

            const itDeskLegMat = new THREE.MeshStandardMaterial({ color: 0x0f172a, metalness: 0.8, roughness: 0.2 });
            [[-0.9, 1.5], [0.9, 1.5], [-0.9, 2.25], [0.9, 2.25]].forEach(lp => {
                const dl = new THREE.Mesh(new THREE.CylinderGeometry(0.025, 0.02, 0.70, 16), itDeskLegMat);
                dl.position.set(lp[0], 0.35, lp[1]);
                itHub.add(dl);
            });

            const chair = new THREE.Mesh(new THREE.BoxGeometry(0.50, 0.06, 0.50), new THREE.MeshStandardMaterial({ color: 0x18181b, roughness: 0.5 }));
            chair.position.set(0, 0.46, 2.45);
            itHub.add(chair);

            const chairBack = new THREE.Mesh(new THREE.BoxGeometry(0.46, 0.50, 0.05), new THREE.MeshStandardMaterial({ color: 0x18181b, roughness: 0.5 }));
            chairBack.position.set(0, 0.74, 2.68);
            itHub.add(chairBack);

            const mon = new THREE.Mesh(new THREE.BoxGeometry(0.65, 0.38, 0.03), new THREE.MeshStandardMaterial({ color: 0x0f172a, emissive: 0x38bdf8, emissiveIntensity: 0.8 }));
            mon.position.set(0, 1.05, 1.65);
            itHub.add(mon);

            this.createNeonSignText(itHub, 0, 3.6, -1.0, '🖥️ Server Room', 0x38bdf8, 3.6);
            parent.add(itHub);
        }

        buildDocumentRoomFacility(parent, x, z) {
            const docRoom = new THREE.Group();
            docRoom.position.set(x, 0, z);

            const cabinetMat = new THREE.MeshStandardMaterial({ color: 0x3d2716, roughness: 0.6 });
            [-1.5, 0, 1.5].forEach(cz => {
                const cab = new THREE.Mesh(new THREE.BoxGeometry(0.65, 1.8, 1.2), cabinetMat);
                cab.position.set(0, 0.9, cz);
                cab.castShadow = true;
                docRoom.add(cab);
            });

            this.createNeonSignText(docRoom, 0, 2.8, 0, '📁 Document Archive', 0xf59e0b, 2.6);
            parent.add(docRoom);
        }

        buildBistroLoungeFacility(parent, x, z) {
            const lounge = new THREE.Group();
            lounge.position.set(x, 0, z);

            const barWoodMat = new THREE.MeshStandardMaterial({ color: 0x271e18, roughness: 0.35 });
            const marbleTopMat = new THREE.MeshStandardMaterial({ color: 0xf8fafc, roughness: 0.15 });
            const stoolSeatMat = new THREE.MeshStandardMaterial({ color: 0xb45309, roughness: 0.6 });
            const metalBlackMat = new THREE.MeshStandardMaterial({ color: 0x0f172a, metalness: 0.8, roughness: 0.2 });

            // 1. Cafe Bar Island Counter (length 3.0m, height 1.02m, depth 0.85m)
            const barBase = new THREE.Mesh(new THREE.BoxGeometry(3.0, 1.0, 0.85), barWoodMat);
            barBase.position.set(0, 0.50, -0.6);
            barBase.castShadow = true;
            lounge.add(barBase);

            const barTop = new THREE.Mesh(new THREE.BoxGeometry(3.2, 0.06, 1.0), marbleTopMat);
            barTop.position.set(0, 1.03, -0.6);
            barTop.castShadow = true;
            lounge.add(barTop);

            // 3 Bar Stools along the front of the counter (z = 0.15)
            [-1.0, 0, 1.0].forEach(sx => {
                const stoolSeat = new THREE.Mesh(new THREE.CylinderGeometry(0.24, 0.24, 0.06, 16), stoolSeatMat);
                stoolSeat.position.set(sx, 0.72, 0.15);
                lounge.add(stoolSeat);

                const stoolPole = new THREE.Mesh(new THREE.CylinderGeometry(0.025, 0.025, 0.70, 12), metalBlackMat);
                stoolPole.position.set(sx, 0.35, 0.15);
                lounge.add(stoolPole);

                const stoolBase = new THREE.Mesh(new THREE.CylinderGeometry(0.22, 0.22, 0.02, 16), metalBlackMat);
                stoolBase.position.set(sx, 0.01, 0.15);
                lounge.add(stoolBase);
            });

            // 2. Coffee Lounge Seating: Low Coffee Table + 2 Cozy Armchairs (z = 1.3)
            const coffeeTable = new THREE.Mesh(new THREE.BoxGeometry(1.4, 0.42, 0.75), marbleTopMat);
            coffeeTable.position.set(0, 0.21, 1.3);
            coffeeTable.castShadow = true;
            lounge.add(coffeeTable);

            const leatherMat = new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.7 });
            [-1.3, 1.3].forEach(ax => {
                const chairBase = new THREE.Mesh(new THREE.BoxGeometry(0.70, 0.42, 0.70), leatherMat);
                chairBase.position.set(ax, 0.21, 1.3);
                chairBase.castShadow = true;
                lounge.add(chairBase);

                const chairBack = new THREE.Mesh(new THREE.BoxGeometry(0.12, 0.42, 0.70), leatherMat);
                chairBack.position.set(ax < 0 ? ax - 0.35 : ax + 0.35, 0.56, 1.3);
                chairBack.castShadow = true;
                lounge.add(chairBack);
            });

            // Decorative Cafe Menu Neon Board on wall
            this.createNeonSignText(lounge, 0, 3.2, -2.4, '☕ Cooca Cafe & Bistro', 0x10b981, 3.2);

            parent.add(lounge);
        }

        buildPantryFacility(parent, x, z) {
            const pantry = new THREE.Group();
            pantry.position.set(x, 0, z);

            const tableTopMat = new THREE.MeshStandardMaterial({ color: 0xe2e8f0, roughness: 0.2 });
            const chairMat = new THREE.MeshStandardMaterial({ color: 0x334155, roughness: 0.5 });
            const counterMat = new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.4 });

            // 3 Round Dining Tables (4 Chairs Each = 12 Chairs Total, matching 2D Blueprint exactly)
            const tablePositions = [
                { x: -1.9, z: -0.95 },
                { x: 1.9, z: -0.95 },
                { x: 0.0, z: 1.25 }
            ];

            tablePositions.forEach(tp => {
                // Table Top at 0.74m
                const table = new THREE.Mesh(new THREE.CylinderGeometry(0.80, 0.80, 0.04, 24), tableTopMat);
                table.position.set(tp.x, 0.74, tp.z);
                table.castShadow = true;
                pantry.add(table);

                const tableLeg = new THREE.Mesh(new THREE.CylinderGeometry(0.045, 0.045, 0.70, 16), new THREE.MeshStandardMaterial({ color: 0x475569 }));
                tableLeg.position.set(tp.x, 0.35, tp.z);
                pantry.add(tableLeg);

                const tableBase = new THREE.Mesh(new THREE.CylinderGeometry(0.35, 0.35, 0.02, 24), new THREE.MeshStandardMaterial({ color: 0x475569 }));
                tableBase.position.set(tp.x, 0.01, tp.z);
                pantry.add(tableBase);

                // 4 Chairs around table (N, S, W, E) seat cushion at 0.46m
                const chairOffsets = [[0, -0.95], [0, 0.95], [-0.95, 0], [0.95, 0]];
                chairOffsets.forEach(co => {
                    const chair = new THREE.Mesh(new THREE.BoxGeometry(0.42, 0.05, 0.42), chairMat);
                    chair.position.set(tp.x + co[0], 0.46, tp.z + co[1]);
                    pantry.add(chair);

                    const chairLeg = new THREE.Mesh(new THREE.CylinderGeometry(0.02, 0.02, 0.42, 12), new THREE.MeshStandardMaterial({ color: 0x18181b }));
                    chairLeg.position.set(tp.x + co[0], 0.22, tp.z + co[1]);
                    pantry.add(chairLeg);
                });
            });

            // Kitchenette Counter along East Partition Wall (standard kitchen counter height 0.90m)
            const counter = new THREE.Mesh(new THREE.BoxGeometry(0.7, 0.90, 4.4), counterMat);
            counter.position.set(4.1, 0.45, 0);
            counter.castShadow = true;
            pantry.add(counter);

            // Red Espresso Machine sitting on top of counter at 0.90m
            const espresso = new THREE.Mesh(new THREE.BoxGeometry(0.45, 0.40, 0.50), new THREE.MeshStandardMaterial({ color: 0xef4444, metalness: 0.5 }));
            espresso.position.set(4.1, 1.10, -1.2);
            pantry.add(espresso);

            // Refrigerator
            const fridge = new THREE.Mesh(new THREE.BoxGeometry(0.65, 2.0, 0.9), new THREE.MeshStandardMaterial({ color: 0xf1f5f9, metalness: 0.2 }));
            fridge.position.set(4.1, 1.0, 1.4);
            pantry.add(fridge);

            this.createNeonSignText(pantry, 0, 3.4, 0, '☕ Pantry & Bistro Lounge', 0x10b981, 3.4);
            parent.add(pantry);
        }

        buildRestroomFacility(parent, x = 16.0, z = 8.8) {
            const rr = new THREE.Group();
            // Positioned at origin so all coordinates directly match campus layout (x: 7.2 to 24.5, z: 6.8 to 10.75)
            rr.position.set(0, 0, 0);

            // =========================================================================
            // A. HIGH-END ARCHITECTURAL RESTROOM MATERIALS (100% Solid & Sanitary)
            // =========================================================================
            const wallMat = new THREE.MeshStandardMaterial({
                color: 0x334155,       // Solid Deep Slate Drywall
                roughness: 0.88,
                metalness: 0.05
            });
            const tileMat = new THREE.MeshStandardMaterial({
                color: 0x1e293b,       // Dark Charcoal Architectural Tile Accent
                roughness: 0.40,
                metalness: 0.15
            });
            const stallPanelMat = new THREE.MeshStandardMaterial({
                color: 0x1e293b,       // Solid Matte Anthracite HPL Compact Laminate
                roughness: 0.72,
                metalness: 0.10
            });
            const doorFrameMat = new THREE.MeshStandardMaterial({
                color: 0x0f172a,       // Anodized Aluminum Headrail & Framing
                roughness: 0.35,
                metalness: 0.80
            });
            const stainlessMat = new THREE.MeshStandardMaterial({
                color: 0xe2e8f0,       // Satin Stainless Steel (Hinges, Grab Bars, Hardware)
                roughness: 0.20,
                metalness: 0.90
            });
            const chromeMat = new THREE.MeshStandardMaterial({
                color: 0xf8fafc,       // Polished Mirror Chrome (Faucets, Sensors, Flush Valves)
                roughness: 0.08,
                metalness: 0.98
            });
            const porcelainMat = new THREE.MeshStandardMaterial({
                color: 0xffffff,       // Glazed Vitreous China (White Ceramic Toilets, Urinals, Basins)
                roughness: 0.10,
                metalness: 0.04
            });
            const vanityTopMat = new THREE.MeshStandardMaterial({
                color: 0x090d16,       // Polished Nero Marquina Black Quartz Slab
                roughness: 0.12,
                metalness: 0.28
            });
            const mirrorMat = new THREE.MeshStandardMaterial({
                color: 0xcfd8dc,       // High-Reflectance Silver Mirror
                roughness: 0.03,
                metalness: 0.98
            });
            const ledHaloMat = new THREE.MeshBasicMaterial({
                color: 0xfef08a        // Warm Ambient LED Glow (2700K)
            });
            const indicatorGreen = new THREE.MeshBasicMaterial({ color: 0x10b981 });
            const indicatorRed   = new THREE.MeshBasicMaterial({ color: 0xef4444 });
            const sensorLensMat  = new THREE.MeshBasicMaterial({ color: 0x0284c7 });

            // =========================================================================
            // B. ARCHITECTURAL WAINSCOTING & BASEBOARD SKIRTING
            // =========================================================================
            // Back Wall (South) Ceramic Tile Wainscoting: spans x = 7.3 to 24.5 at z = 9.88, height = 2.4m
            const backWainscot = new THREE.Mesh(new THREE.BoxGeometry(17.2, 2.4, 0.03), tileMat);
            backWainscot.position.set(15.9, 1.2, 9.88);
            backWainscot.receiveShadow = true;
            rr.add(backWainscot);

            // Stainless Steel Skirting Baseboard along South Wall
            const baseboardS = new THREE.Mesh(new THREE.BoxGeometry(17.2, 0.14, 0.04), stainlessMat);
            baseboardS.position.set(15.9, 0.07, 9.87);
            rr.add(baseboardS);

            // Stainless Steel Skirting Baseboard along West Wall (x = 7.23, z = 6.8 to 9.9)
            const baseboardW = new THREE.Mesh(new THREE.BoxGeometry(0.04, 0.14, 3.1), stainlessMat);
            baseboardW.position.set(7.23, 0.07, 8.35);
            rr.add(baseboardW);

            // Stainless Steel Skirting Baseboard along East Wall (x = 24.47, z = 6.8 to 9.9)
            const baseboardE = new THREE.Mesh(new THREE.BoxGeometry(0.04, 0.14, 3.1), stainlessMat);
            baseboardE.position.set(24.47, 0.07, 8.35);
            rr.add(baseboardE);

            // =========================================================================
            // C. 5 REALISTIC SOLID TOILET CUBICLES (BILIK TOILET SOLID HPL)
            // Stalls line the South Wall from x = 7.5 to 15.25, depth: z = 8.2 to 9.85
            // 100% Solid Panels, Pedestal Feet, Headrail, Hinged Solid Doors, Occupancy Dial
            // =========================================================================
            const stallDefs = [
                { id: 1, name: 'Accessible / Executive Master', minX: 7.50, maxX: 9.15, doorW: 0.90, occupied: false, ajar: 0.0,  accessible: true },
                { id: 2, name: 'Standard Stall 2',              minX: 9.15, maxX: 10.65, doorW: 0.78, occupied: true,  ajar: 0.0,  accessible: false },
                { id: 3, name: 'Standard Stall 3',              minX: 10.65, maxX: 12.15, doorW: 0.78, occupied: false, ajar: 0.0,  accessible: false },
                { id: 4, name: 'Standard Stall 4',              minX: 12.15, maxX: 13.65, doorW: 0.78, occupied: false, ajar: 0.42, accessible: false }, // Slightly ajar to show interior
                { id: 5, name: 'Standard Stall 5',              minX: 13.65, maxX: 15.15, doorW: 0.78, occupied: false, ajar: 0.0,  accessible: false }
            ];

            const stallDepth = 1.65;      // Stall depth along Z (8.20 to 9.85)
            const stallZFront = 8.20;
            const stallZBack  = 9.85;
            const stallZCenter = (stallZFront + stallZBack) / 2;
            const panelHeight = 2.05;     // Panel starts at y = 0.15, ends at y = 2.20
            const panelElevation = 0.15;  // Raised 15cm off floor for commercial hygiene

            // Continuous Top Headrail running across all stalls for structural rigidity
            const headrail = new THREE.Mesh(new THREE.BoxGeometry(7.75, 0.06, 0.06), doorFrameMat);
            headrail.position.set(11.325, panelElevation + panelHeight + 0.03, stallZFront);
            rr.add(headrail);

            // Dividing Walls (Solid Partition Slabs between stalls)
            const dividerXPositions = [7.50, 9.15, 10.65, 12.15, 13.65, 15.15];
            dividerXPositions.forEach((dx) => {
                // Solid Dividing Slab
                const divider = new THREE.Mesh(new THREE.BoxGeometry(0.04, panelHeight, stallDepth), stallPanelMat);
                divider.position.set(dx, panelElevation + panelHeight / 2, stallZCenter);
                divider.castShadow = true;
                divider.receiveShadow = true;
                rr.add(divider);

                // Stainless Steel Pedestal Feet (Front & Rear)
                [stallZFront + 0.12, stallZBack - 0.15].forEach((fz) => {
                    const foot = new THREE.Mesh(new THREE.CylinderGeometry(0.02, 0.02, panelElevation, 16), stainlessMat);
                    foot.position.set(dx, panelElevation / 2, fz);
                    rr.add(foot);

                    const footFlange = new THREE.Mesh(new THREE.CylinderGeometry(0.04, 0.04, 0.02, 16), stainlessMat);
                    footFlange.position.set(dx, 0.01, fz);
                    rr.add(footFlange);
                });
            });

            // Build Individual Stall Fronts, Doors, and Detailed Interiors
            stallDefs.forEach((stall) => {
                const stallWidth = stall.maxX - stall.minX;
                const stallCenterX = (stall.minX + stall.maxX) / 2;
                const pilasterW = (stallWidth - stall.doorW) / 2;

                // 1. Solid Left & Right Front Pilasters
                if (pilasterW > 0.05) {
                    [stall.minX + pilasterW / 2, stall.maxX - pilasterW / 2].forEach((px) => {
                        const pilaster = new THREE.Mesh(new THREE.BoxGeometry(pilasterW, panelHeight, 0.04), stallPanelMat);
                        pilaster.position.set(px, panelElevation + panelHeight / 2, stallZFront);
                        pilaster.castShadow = true;
                        rr.add(pilaster);

                        const pFoot = new THREE.Mesh(new THREE.CylinderGeometry(0.018, 0.018, panelElevation, 16), stainlessMat);
                        pFoot.position.set(px, panelElevation / 2, stallZFront);
                        rr.add(pFoot);
                    });
                }

                // 2. Solid Cubicle Door Leaf
                const doorLeafGroup = new THREE.Group();
                const doorHingeX = stall.minX + pilasterW;
                doorLeafGroup.position.set(doorHingeX, 0, stallZFront);

                const doorLeaf = new THREE.Mesh(new THREE.BoxGeometry(stall.doorW - 0.02, panelHeight - 0.04, 0.035), stallPanelMat);
                doorLeaf.position.set((stall.doorW - 0.02) / 2, panelElevation + panelHeight / 2, 0);
                doorLeaf.castShadow = true;
                doorLeafGroup.add(doorLeaf);

                // Heavy-Duty Stainless Steel Hinges (Top, Mid, Bottom)
                [panelElevation + 0.35, panelElevation + panelHeight / 2, panelElevation + panelHeight - 0.35].forEach((hy) => {
                    const hinge = new THREE.Mesh(new THREE.CylinderGeometry(0.014, 0.014, 0.09, 12), stainlessMat);
                    hinge.position.set(0, hy, 0.02);
                    doorLeafGroup.add(hinge);
                });

                // Stainless Lever Latch / Handle on Door
                const handleX = stall.doorW - 0.08;
                const handleY = 1.05;
                const latch = new THREE.Mesh(new THREE.CylinderGeometry(0.012, 0.012, 0.14, 12), stainlessMat);
                latch.rotation.z = Math.PI / 2;
                latch.position.set(handleX, handleY, -0.03);
                doorLeafGroup.add(latch);

                const latchMount = new THREE.Mesh(new THREE.CylinderGeometry(0.025, 0.025, 0.02, 16), stainlessMat);
                latchMount.rotation.x = Math.PI / 2;
                latchMount.position.set(handleX - 0.05, handleY, -0.02);
                doorLeafGroup.add(latchMount);

                // Exterior Vacancy Indicator Disc (Green = Vacant, Red = Occupied)
                const indicatorDisc = new THREE.Mesh(
                    new THREE.CircleGeometry(0.028, 16),
                    stall.occupied ? indicatorRed : indicatorGreen
                );
                indicatorDisc.rotation.y = Math.PI; // Faces corridor (North)
                indicatorDisc.position.set(handleX - 0.05, handleY + 0.08, -0.022);
                doorLeafGroup.add(indicatorDisc);

                // Interior Stainless Coat Hook
                const hook = new THREE.Mesh(new THREE.CylinderGeometry(0.008, 0.008, 0.06, 12), stainlessMat);
                hook.rotation.x = -Math.PI / 4;
                hook.position.set(stall.doorW / 2, 1.65, 0.03);
                doorLeafGroup.add(hook);

                // Door Swing Angle (Ajar if specified)
                doorLeafGroup.rotation.y = stall.ajar;
                rr.add(doorLeafGroup);

                // 3. Ceramic Vitreous China Toilet Bowl (Kloset Duduk Keramik)
                const toiletGroup = new THREE.Group();
                toiletGroup.position.set(stallCenterX, 0, stallZBack - 0.38);

                // Toilet Base / Ceramic Pedestal
                const toiletPedestal = new THREE.Mesh(new THREE.BoxGeometry(0.36, 0.36, 0.48), porcelainMat);
                toiletPedestal.position.set(0, 0.18, 0);
                toiletPedestal.castShadow = true;
                toiletGroup.add(toiletPedestal);

                // Rounded Front Ceramic Bowl
                const toiletBowlRim = new THREE.Mesh(new THREE.CylinderGeometry(0.18, 0.15, 0.12, 24), porcelainMat);
                toiletBowlRim.position.set(0, 0.38, 0.08);
                toiletBowlRim.castShadow = true;
                toiletGroup.add(toiletBowlRim);

                // Ergonomic Toilet Seat Ring (Glossy White Ceramic/Polypropylene)
                const seatRing = new THREE.Mesh(new THREE.TorusGeometry(0.14, 0.035, 12, 24), porcelainMat);
                seatRing.rotation.x = Math.PI / 2;
                seatRing.position.set(0, 0.44, 0.08);
                toiletGroup.add(seatRing);

                // Upright Lid resting back
                const toiletLid = new THREE.Mesh(new THREE.BoxGeometry(0.32, 0.38, 0.03), porcelainMat);
                toiletLid.rotation.x = -0.15;
                toiletLid.position.set(0, 0.62, -0.12);
                toiletGroup.add(toiletLid);

                // Chrome Dual-Flush Wall Actuator Plate (Plate with Eco & Full Flush Buttons)
                const flushPlate = new THREE.Mesh(new THREE.BoxGeometry(0.24, 0.15, 0.015), chromeMat);
                flushPlate.position.set(0, 0.88, -0.22);
                toiletGroup.add(flushPlate);

                [-0.04, 0.04].forEach((bx, bIdx) => {
                    const btn = new THREE.Mesh(new THREE.CylinderGeometry(bIdx === 0 ? 0.022 : 0.030, bIdx === 0 ? 0.022 : 0.030, 0.015, 16), chromeMat);
                    btn.rotation.x = Math.PI / 2;
                    btn.position.set(bx, 0.88, -0.21);
                    toiletGroup.add(btn);
                });

                // Dual-Roll Commercial Stainless Toilet Paper Dispenser (Mounted on side panel)
                const tpDispenser = new THREE.Mesh(new THREE.BoxGeometry(0.22, 0.14, 0.12), stainlessMat);
                tpDispenser.position.set(-stallWidth / 2 + 0.12, 0.72, 0);
                toiletGroup.add(tpDispenser);

                // Visible Toilet Paper Rolls
                [-0.05, 0.05].forEach((rx) => {
                    const tpRoll = new THREE.Mesh(new THREE.CylinderGeometry(0.045, 0.045, 0.08, 16), new THREE.MeshStandardMaterial({ color: 0xf8fafc, roughness: 0.9 }));
                    tpRoll.rotation.z = Math.PI / 2;
                    tpRoll.position.set(-stallWidth / 2 + 0.12 + rx, 0.72, 0.02);
                    toiletGroup.add(tpRoll);
                });

                // Stainless Steel Sanitary Pedal Waste Bin
                const trashBin = new THREE.Mesh(new THREE.CylinderGeometry(0.10, 0.10, 0.28, 16), stainlessMat);
                trashBin.position.set(stallWidth / 2 - 0.20, 0.14, 0.10);
                trashBin.castShadow = true;
                toiletGroup.add(trashBin);

                const trashLid = new THREE.Mesh(new THREE.CylinderGeometry(0.105, 0.105, 0.02, 16), chromeMat);
                trashLid.position.set(stallWidth / 2 - 0.20, 0.29, 0.10);
                toiletGroup.add(trashLid);

                // Accessible Stall 1 Stainless Grab Bars (Safety Handrails)
                if (stall.accessible) {
                    // Side wall horizontal safety rail
                    const grabBarSide = new THREE.Mesh(new THREE.CylinderGeometry(0.018, 0.018, 0.85, 16), stainlessMat);
                    grabBarSide.rotation.x = Math.PI / 2;
                    grabBarSide.position.set(-stallWidth / 2 + 0.06, 0.82, 0);
                    toiletGroup.add(grabBarSide);

                    // Back wall horizontal safety rail
                    const grabBarBack = new THREE.Mesh(new THREE.CylinderGeometry(0.018, 0.018, 0.65, 16), stainlessMat);
                    grabBarBack.rotation.z = Math.PI / 2;
                    grabBarBack.position.set(0.15, 0.82, -0.20);
                    toiletGroup.add(grabBarBack);
                }

                rr.add(toiletGroup);

                // Recessed Ceiling Downlight disc above each cubicle
                const ceilingSpot = new THREE.Mesh(new THREE.CylinderGeometry(0.08, 0.08, 0.02, 16), chromeMat);
                ceilingSpot.position.set(stallCenterX, 3.75, stallZCenter);
                rr.add(ceilingSpot);

                const spotHalo = new THREE.Mesh(new THREE.CircleGeometry(0.06, 16), ledHaloMat);
                spotHalo.rotation.x = Math.PI / 2;
                spotHalo.position.set(stallCenterX, 3.738, stallZCenter);
                rr.add(spotHalo);
            });

            // Register Stalls Block Collider (Prevents clipping through stalls)
            this.wallColliders.push({
                minX: 7.40,
                maxX: 15.25,
                minZ: 8.10,
                maxZ: 9.90
            });

            // =========================================================================
            // D. URINAL SECTION WITH SOLID PRIVACY SCREENS (AREA URINOIR EKSEKUTIF)
            // Positioned along South Wall from x = 16.3 to 19.0, z = 9.75
            // 3 Wall-Hung Ceramic Urinals, 2 Solid Privacy Screens, Chrome Sensors
            // =========================================================================
            const urinalPositions = [16.85, 17.65, 18.45];
            urinalPositions.forEach((ux) => {
                const urinalGroup = new THREE.Group();
                urinalGroup.position.set(ux, 0, 9.72);

                // Wall-Hung Contoured Porcelain Urinal Body
                const uBody = new THREE.Mesh(new THREE.BoxGeometry(0.36, 0.65, 0.28), porcelainMat);
                uBody.position.set(0, 0.78, -0.14);
                uBody.castShadow = true;
                urinalGroup.add(uBody);

                // Curved Lip Bowl
                const uLip = new THREE.Mesh(new THREE.CylinderGeometry(0.18, 0.12, 0.16, 16), porcelainMat);
                uLip.position.set(0, 0.52, -0.16);
                urinalGroup.add(uLip);

                // Chrome Top Sensor Flush Valve Pipe
                const flushPipe = new THREE.Mesh(new THREE.CylinderGeometry(0.014, 0.014, 0.38, 12), chromeMat);
                flushPipe.position.set(0, 1.25, -0.06);
                urinalGroup.add(flushPipe);

                // Electronic Sensor Lens Module
                const sensorBox = new THREE.Mesh(new THREE.BoxGeometry(0.06, 0.08, 0.05), chromeMat);
                sensorBox.position.set(0, 1.35, -0.06);
                urinalGroup.add(sensorBox);

                const sensorEye = new THREE.Mesh(new THREE.CircleGeometry(0.012, 12), sensorLensMat);
                sensorEye.rotation.y = Math.PI;
                sensorEye.position.set(0, 1.35, -0.086);
                urinalGroup.add(sensorEye);

                // Stainless Floor Mat / Grate underneath
                const floorMat = new THREE.Mesh(new THREE.BoxGeometry(0.55, 0.01, 0.55), tileMat);
                floorMat.position.set(0, 0.005, -0.32);
                urinalGroup.add(floorMat);

                rr.add(urinalGroup);
            });

            // 2 SOLID PRIVACY SCREENS BETWEEN URINALS (PEMBATAS SOLID URINOIR)
            // Suspended on stainless brackets at y = 1.05m, depth 0.45m
            [17.25, 18.05].forEach((sx) => {
                const screenPanel = new THREE.Mesh(new THREE.BoxGeometry(0.035, 0.95, 0.48), stallPanelMat);
                screenPanel.position.set(sx, 1.05, 9.46);
                screenPanel.castShadow = true;
                screenPanel.receiveShadow = true;
                rr.add(screenPanel);

                // 2 Stainless Steel Wall Mounting Brackets
                [0.75, 1.35].forEach((by) => {
                    const bracket = new THREE.Mesh(new THREE.BoxGeometry(0.045, 0.05, 0.08), stainlessMat);
                    bracket.position.set(sx, by, 9.72);
                    rr.add(bracket);
                });
            });

            // SOLID ARCHITECTURAL MODESTY BAFFLE SCREEN (PEMBATAS PRIVASI PINTU MASUK)
            // Located at x = 19.10, spanning z = 9.85 to 8.35, height 2.6m
            // Shields urinals and stalls from the direct line-of-sight of the entrance door!
            const baffleLen = 1.50;
            const baffleZ = (9.85 + 8.35) / 2;
            const baffleWall = new THREE.Mesh(new THREE.BoxGeometry(0.08, 2.60, baffleLen), wallMat);
            baffleWall.position.set(19.10, 1.30, baffleZ);
            baffleWall.castShadow = true;
            baffleWall.receiveShadow = true;
            rr.add(baffleWall);

            // Stainless Steel Skirting Kickplate at bottom of baffle
            const baffleKick = new THREE.Mesh(new THREE.BoxGeometry(0.10, 0.12, baffleLen), stainlessMat);
            baffleKick.position.set(19.10, 0.06, baffleZ);
            rr.add(baffleKick);

            // Top Architectural Trim Profile on baffle
            const baffleTrim = new THREE.Mesh(new THREE.BoxGeometry(0.10, 0.06, baffleLen), doorFrameMat);
            baffleTrim.position.set(19.10, 2.57, baffleZ);
            rr.add(baffleTrim);

            // Register Urinal and Baffle Colliders
            this.wallColliders.push({
                minX: 16.50,
                maxX: 19.00,
                minZ: 9.15,
                maxZ: 9.90
            });
            this.wallColliders.push({
                minX: 18.95,
                maxX: 19.25,
                minZ: 8.30,
                maxZ: 9.90
            });

            // =========================================================================
            // E. GRAND HANDWASHING VANITY STATION (WASTAFEL MARMER & CERMIN BACKLIT)
            // Positioned along South Wall from x = 20.60 to 24.20 (Length = 3.60m)
            // 4 Ceramic Basins, Gooseneck Sensor Faucets, Soap Dispensers, LED Mirror
            // =========================================================================
            const vanityW = 3.60;
            const vanityD = 0.68;
            const vanityH = 0.86;
            const vanityCenterX = 22.40;
            const vanityCenterZ = 9.85 - vanityD / 2; // ~9.51

            // 1. Polished Nero Marquina Quartz Slab Countertop
            const counterTop = new THREE.Mesh(new THREE.BoxGeometry(vanityW, 0.06, vanityD), vanityTopMat);
            counterTop.position.set(vanityCenterX, vanityH, vanityCenterZ);
            counterTop.castShadow = true;
            counterTop.receiveShadow = true;
            rr.add(counterTop);

            // 2. Under-counter Vanity Cabinet Apron with subtle shadow gap
            const vanityApron = new THREE.Mesh(new THREE.BoxGeometry(vanityW - 0.08, 0.62, vanityD - 0.06), new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.6 }));
            vanityApron.position.set(vanityCenterX, 0.46, vanityCenterZ - 0.02);
            vanityApron.castShadow = true;
            rr.add(vanityApron);

            // 3. 4 Undermount Porcelain Washbasins with Gooseneck Sensor Faucets
            const basinSpacing = vanityW / 4;
            const basinStartX = vanityCenterX - vanityW / 2 + basinSpacing / 2;

            for (let i = 0; i < 4; i++) {
                const bx = basinStartX + i * basinSpacing;

                // White Ceramic Basin Rim & Interior
                const basin = new THREE.Mesh(new THREE.BoxGeometry(0.48, 0.04, 0.38), porcelainMat);
                basin.position.set(bx, vanityH + 0.025, vanityCenterZ);
                rr.add(basin);

                const basinRecess = new THREE.Mesh(new THREE.CylinderGeometry(0.18, 0.14, 0.12, 24), porcelainMat);
                basinRecess.position.set(bx, vanityH - 0.05, vanityCenterZ);
                rr.add(basinRecess);

                // Chrome Pop-up Drain Stopper
                const drain = new THREE.Mesh(new THREE.CylinderGeometry(0.028, 0.028, 0.015, 16), chromeMat);
                drain.position.set(bx, vanityH - 0.10, vanityCenterZ);
                rr.add(drain);

                // Tall Modern Curved Gooseneck Sensor Faucet
                const faucetArch = new THREE.Mesh(new THREE.TorusGeometry(0.10, 0.014, 12, 24, Math.PI), chromeMat);
                faucetArch.rotation.z = Math.PI / 2;
                faucetArch.position.set(bx, vanityH + 0.18, vanityCenterZ + 0.14);
                faucetArch.castShadow = true;
                rr.add(faucetArch);

                const faucetStem = new THREE.Mesh(new THREE.CylinderGeometry(0.016, 0.018, 0.14, 16), chromeMat);
                faucetStem.position.set(bx, vanityH + 0.10, vanityCenterZ + 0.22);
                rr.add(faucetStem);

                // Infrared Proximity Sensor Dot
                const faucetSensor = new THREE.Mesh(new THREE.CircleGeometry(0.008, 12), sensorLensMat);
                faucetSensor.rotation.x = -Math.PI / 3;
                faucetSensor.position.set(bx, vanityH + 0.12, vanityCenterZ + 0.18);
                rr.add(faucetSensor);

                // Automatic Stainless Soap Dispenser
                const soapDispenser = new THREE.Mesh(new THREE.CylinderGeometry(0.02, 0.02, 0.12, 16), stainlessMat);
                soapDispenser.position.set(bx + 0.24, vanityH + 0.09, vanityCenterZ + 0.16);
                rr.add(soapDispenser);

                const soapSpout = new THREE.Mesh(new THREE.CylinderGeometry(0.006, 0.006, 0.07, 12), chromeMat);
                soapSpout.rotation.z = Math.PI / 3;
                soapSpout.position.set(bx + 0.21, vanityH + 0.14, vanityCenterZ + 0.13);
                rr.add(soapSpout);
            }

            // 4. Large Floating LED Backlit Mirror
            const mirrorW = vanityW;
            const mirrorH = 1.35;
            const mirrorY = 1.82;
            const mirrorZ = 9.84;

            // Mirror Pane
            const mirror = new THREE.Mesh(new THREE.BoxGeometry(mirrorW, mirrorH, 0.02), mirrorMat);
            mirror.position.set(vanityCenterX, mirrorY, mirrorZ);
            rr.add(mirror);

            // Glowing Warm LED Halo Frame (Rim glow behind mirror)
            const ledHalo = new THREE.Mesh(new THREE.BoxGeometry(mirrorW + 0.06, mirrorH + 0.06, 0.01), ledHaloMat);
            ledHalo.position.set(vanityCenterX, mirrorY, mirrorZ + 0.008);
            rr.add(ledHalo);

            // Register Vanity Counter Collider
            this.wallColliders.push({
                minX: 20.50,
                maxX: 24.30,
                minZ: 9.05,
                maxZ: 9.90
            });

            // =========================================================================
            // F. HIGH-SPEED JET HAND DRYERS & HYGIENE STATION (EAST WALL)
            // Mounted on East Wall at x = 24.40, z = 7.90 and 8.70
            // Dyson Airblade Style in Brushed Stainless Steel
            // =========================================================================
            [7.90, 8.70].forEach((dz) => {
                const dryerGroup = new THREE.Group();
                dryerGroup.position.set(24.40, 1.15, dz);

                // Main Stainless Steel Housing
                const dryerBody = new THREE.Mesh(new THREE.BoxGeometry(0.12, 0.62, 0.32), stainlessMat);
                dryerBody.position.set(-0.06, 0, 0);
                dryerBody.castShadow = true;
                dryerGroup.add(dryerBody);

                // Hand Insertion Cavity (Dark Inset)
                const dryerCavity = new THREE.Mesh(new THREE.BoxGeometry(0.08, 0.35, 0.24), new THREE.MeshStandardMaterial({ color: 0x0f172a, roughness: 0.9 }));
                dryerCavity.position.set(-0.08, 0.06, 0);
                dryerGroup.add(dryerCavity);

                // Blue LED Airblade Guide Light
                const dryerLight = new THREE.Mesh(new THREE.BoxGeometry(0.02, 0.015, 0.22), new THREE.MeshBasicMaterial({ color: 0x38bdf8 }));
                dryerLight.position.set(-0.11, 0.22, 0);
                dryerGroup.add(dryerLight);

                rr.add(dryerGroup);
            });

            // Stainless Steel Paper Towel Dispenser & Waste Receptacle
            const paperCabinet = new THREE.Mesh(new THREE.BoxGeometry(0.08, 0.95, 0.38), stainlessMat);
            paperCabinet.position.set(24.40, 1.25, 7.15);
            rr.add(paperCabinet);

            this.wallColliders.push({
                minX: 24.15,
                maxX: 24.55,
                minZ: 7.00,
                maxZ: 9.00
            });

            // =========================================================================
            // G. CORRIDOR CEILING SPOTLIGHTS & SIGNAGE
            // =========================================================================
            // Row of Warm LED Recessed Downlights along the Walkway Corridor (z = 7.6)
            [8.5, 10.8, 13.1, 15.4, 17.7, 20.0, 22.3].forEach((lx) => {
                const spotRim = new THREE.Mesh(new THREE.CylinderGeometry(0.09, 0.09, 0.02, 16), chromeMat);
                spotRim.position.set(lx, 3.75, 7.60);
                rr.add(spotRim);

                const spotGlow = new THREE.Mesh(new THREE.CircleGeometry(0.07, 16), ledHaloMat);
                spotGlow.rotation.x = Math.PI / 2;
                spotGlow.position.set(lx, 3.738, 7.60);
                rr.add(spotGlow);
            });

            // Refined Restroom Signage Plaque near Entrance Doorway
            this.createNeonSignText(rr, 19.5, 3.2, 6.85, '🚻 Restroom • Executive Facilities', 0x94a3b8, 3.4);

            parent.add(rr);
        }

        buildPottedPlant(parent, x, z, type = 'monstera') {
            const plantGroup = new THREE.Group();
            plantGroup.position.set(x, 0, z);

            const potMat = new THREE.MeshStandardMaterial({ color: 0xf8fafc, roughness: 0.2 });
            const leafMat = new THREE.MeshStandardMaterial({ color: 0x15803d, roughness: 0.4, side: THREE.DoubleSide });

            const pot = new THREE.Mesh(new THREE.CylinderGeometry(0.5, 0.35, 0.8, 16), potMat);
            pot.position.y = 0.4;
            pot.castShadow = true;
            plantGroup.add(pot);

            if (type === 'monstera') {
                for (let l = 0; l < 6; l++) {
                    const leaf = new THREE.Mesh(new THREE.CircleGeometry(0.4, 12), leafMat);
                    const ang = (l * Math.PI * 2) / 6;
                    leaf.position.set(Math.cos(ang) * 0.3, 0.9 + l * 0.08, Math.sin(ang) * 0.3);
                    leaf.rotation.x = Math.PI / 3;
                    leaf.rotation.y = ang;
                    plantGroup.add(leaf);
                }
            } else {
                const trunk = new THREE.Mesh(new THREE.CylinderGeometry(0.07, 0.1, 1.4), new THREE.MeshStandardMaterial({ color: 0x78350f, roughness: 0.8 }));
                trunk.position.y = 1.2;
                plantGroup.add(trunk);
                for (let i = 0; i < 3; i++) {
                    const foliage = new THREE.Mesh(new THREE.DodecahedronGeometry(0.55 - i * 0.1, 1), leafMat);
                    foliage.position.set((Math.random() - 0.5) * 0.2, 1.7 + i * 0.35, (Math.random() - 0.5) * 0.2);
                    plantGroup.add(foliage);
                }
            }

            parent.add(plantGroup);
        }

        createNeonSignText(parent, x, y, z, text, glowColor, width = 3.0) {
            const canvas = document.createElement('canvas');
            canvas.width = 512;
            canvas.height = 100;
            const ctx = canvas.getContext('2d');

            ctx.fillStyle = 'rgba(15, 23, 42, 0.85)';
            this.roundRect(ctx, 10, 10, 492, 80, 20);
            ctx.fill();

            ctx.strokeStyle = this.hexToRgba(glowColor, 0.8);
            ctx.lineWidth = 3;
            ctx.stroke();

            ctx.fillStyle = '#ffffff';
            ctx.font = 'bold 30px Inter, sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText(text, 256, 58);

            const texture = new THREE.CanvasTexture(canvas);
            const spriteMat = new THREE.SpriteMaterial({ map: texture, transparent: true });
            const sprite = new THREE.Sprite(spriteMat);
            sprite.position.set(x, y, z);
            sprite.scale.set(width, 0.6, 1);
            parent.add(sprite);
        }

        // ==========================================
        // 3. ALL TEAM WORKSTATIONS (100% 2D BLUEPRINT SVG LAYOUT)
        // ==========================================
        buildAllTeamWorkstations() {
            // Placement map for ALL 17 AI Agents distributed on 1 single floor (100% 2D Blueprint SVG exact matching)
            const teamLayout = {
                // --- EXECUTIVE SUITE (Top-Center: x = -7.2 to 7.2, z = -15.75 to -4.2) ---
                'ceo':            { x: 0.0, y: 0, z: -13.5, rot: 0, team: 'executive', isLead: true, title: 'AI CEO', roleBadge: 'Leading' },
                'cfo':            { x: -4.5, y: 0, z: -12.5, rot: 0, team: 'executive', isLead: true, title: 'AI CFO', roleBadge: 'Working' },
                'business':       { x: 4.5, y: 0, z: -12.5, rot: 0, team: 'executive', isLead: false, title: 'Business Agent', roleBadge: 'Insights' },

                // --- MARKETING ROOM (Top-West: x = -24.75 to -7.2, z = -15.75 to -5.0) ---
                'cmo':            { x: -20.5, y: 0, z: -13.5, rot: 0, team: 'marketing', isLead: true, title: 'AI CMO', roleBadge: 'Directing' },
                'marketing':      { x: -20.5, y: 0, z: -11.5, rot: 0, team: 'marketing', isLead: false, title: 'Marketing Agent', roleBadge: 'Campaigns' },
                'content':        { x: -16.0, y: 0, z: -11.5, rot: 0, team: 'marketing', isLead: false, title: 'Content Agent', roleBadge: 'Copywriting' },
                'social_media':   { x: -11.5, y: 0, z: -11.5, rot: 0, team: 'marketing', isLead: false, title: 'Social Media Agent', roleBadge: 'Engaging' },

                // --- SALES ROOM (Mid-West: x = -24.75 to -7.2, z = -5.0 to 2.5) ---
                'sales_director': { x: -15.5, y: 0, z: -4.2, rot: 0, team: 'sales', isLead: true, title: 'Sales Director', roleBadge: 'Pipeline' },
                'sales':          { x: -18.0, y: 0, z: -2.2, rot: 0, team: 'sales', isLead: false, title: 'Sales Agent', roleBadge: 'Deals' },
                'customer':       { x: -13.0, y: 0, z: -2.2, rot: 0, team: 'sales', isLead: false, title: 'Customer Agent', roleBadge: 'Support' },

                // --- OPERATIONS & TECH / SERVER WING (Top-East: x = 7.2 to 24.75, z = -15.75 to -5.0) ---
                'coo':            { x: 12.6, y: 0, z: -13.5, rot: 0, team: 'operations', isLead: true, title: 'AI COO', roleBadge: 'Operations' },
                'inventory':      { x: 9.6, y: 0, z: -11.5, rot: 0, team: 'operations', isLead: false, title: 'Inventory Agent', roleBadge: 'Stock' },
                'purchasing':     { x: 12.6, y: 0, z: -11.5, rot: 0, team: 'operations', isLead: false, title: 'Purchasing Agent', roleBadge: 'Orders' },
                'marketplace':    { x: 15.6, y: 0, z: -11.5, rot: 0, team: 'operations', isLead: false, title: 'Marketplace Agent', roleBadge: 'Sync' },
                'hr':             { x: 16.0, y: 0, z: -7.5, rot: 0, team: 'people', isLead: false, title: 'HR Agent', roleBadge: 'Workforce' },

                // --- FINANCE ROOM (Mid-East Mid: x = 7.2 to 18.0, z = -5.0 to 1.5) ---
                'finance':        { x: 10.5, y: 0, z: -1.8, rot: 0, team: 'finance', isLead: false, title: 'Finance Agent', roleBadge: 'Auditing' },
                'reporting':      { x: 14.5, y: 0, z: -1.8, rot: 0, team: 'finance', isLead: false, title: 'Reporting Agent', roleBadge: 'Compiling' },
            };

            Object.entries(teamLayout).forEach(([roleKey, pos]) => {
                const agentData = this.agents[roleKey] || {
                    name: pos.title,
                    role: roleKey,
                    status: 'idle',
                    department: pos.team,
                    team: pos.team
                };

                const workstation = this.createSimsAgentWorkstation(roleKey, agentData, pos);
                this.scene.add(workstation);
                this.agentMeshes.set(roleKey, workstation);
            });
        }

        initLiveMetricsPolling() {
            if (typeof window === 'undefined' || !window.fetch) return;
            this.pollInterval = setInterval(() => {
                fetch(this.liveMetricsUrl)
                    .then(res => res.json())
                    .then(json => {
                        if (json && json.success && json.metrics) {
                            this.businessData = json.metrics;
                            this.redrawAllMonitors();
                        }
                    })
                    .catch(() => {});
            }, 30000);
        }

        redrawAllMonitors() {
            if (!this.liveMonitors || this.liveMonitors.length === 0) return;
            this.liveMonitors.forEach((m) => {
                if (m.type === 'primary') {
                    this.renderPrimaryMonitorUI(m.ctx, m.roleKey, m.team, 0);
                } else {
                    this.renderSecondaryMonitorUI(m.ctx, m.roleKey, 0);
                }
                if (m.texture) {
                    m.texture.needsUpdate = true;
                }
            });
        }

        // ==========================================
        // DYNAMIC WORKING MONITOR ENGINE (LIVE CANVASTEXTURES)
        // ==========================================
        createPrimaryMonitorCanvas(roleKey, team) {
            const canvas = document.createElement('canvas');
            canvas.width = 256;
            canvas.height = 160;
            const ctx = canvas.getContext('2d');
            this.renderPrimaryMonitorUI(ctx, roleKey, team, 0);
            return canvas;
        }

        createSecondaryMonitorCanvas(roleKey) {
            const canvas = document.createElement('canvas');
            canvas.width = 192;
            canvas.height = 140;
            const ctx = canvas.getContext('2d');
            this.renderSecondaryMonitorUI(ctx, roleKey, 0);
            return canvas;
        }

        renderPrimaryMonitorUI(ctx, roleKey, team, elapsed) {
            const w = 256;
            const h = 160;

            // Deep Modern OS Dark Background
            ctx.fillStyle = '#080d1a';
            ctx.fillRect(0, 0, w, h);

            // Title Bar
            ctx.fillStyle = '#0f172a';
            ctx.fillRect(0, 0, w, 22);

            // Mac/Unix Traffic Light Dots
            ctx.fillStyle = '#ef4444'; ctx.beginPath(); ctx.arc(10, 11, 3.5, 0, Math.PI * 2); ctx.fill();
            ctx.fillStyle = '#f59e0b'; ctx.beginPath(); ctx.arc(20, 11, 3.5, 0, Math.PI * 2); ctx.fill();
            ctx.fillStyle = '#10b981'; ctx.beginPath(); ctx.arc(30, 11, 3.5, 0, Math.PI * 2); ctx.fill();

            // Role Titles Map
            const roleHeaders = {
                ceo: 'CEO | EXECUTIVE STRATEGY',
                cfo: 'CFO | TREASURY & RUNWAY',
                business: 'BI | COHORT & RETENTION',
                cmo: 'CMO | GROWTH OMNICHANNEL',
                marketing: 'MKT | ADS PERFORMANCE',
                content: 'CONTENT | SEO & SERP RANK',
                social_media: 'SOCIAL | VIRAL ENGAGEMENT',
                sales_director: 'SALES DIR | QUOTA PIPELINE',
                sales: 'SALES | DEAL FUNNEL CRM',
                customer: 'CS | CSAT & SLA SUPPORT',
                coo: 'COO | OPERATIONS COMMAND',
                inventory: 'INVENTORY | SKU WATCHER',
                purchasing: 'PURCHASING | VENDOR RFQ',
                marketplace: 'MARKETPLACE | 4-CHANNEL SYNC',
                finance: 'FINANCE | LEDGER & AUDIT',
                reporting: 'REPORTING | P&L & TAX MATRIX',
                hr: 'HR | PEOPLE & PAYROLL'
            };

            const headerTitle = roleHeaders[roleKey] || `${roleKey.toUpperCase()} // DASHBOARD`;
            ctx.fillStyle = '#38bdf8';
            ctx.font = 'bold 9.5px monospace';
            ctx.fillText(headerTitle, 42, 15);

            // Live Green Beacon
            ctx.fillStyle = '#10b981';
            ctx.beginPath(); ctx.arc(242, 11, 3.5, 0, Math.PI * 2); ctx.fill();
            ctx.fillStyle = '#94a3b8';
            ctx.font = '7.5px sans-serif';
            ctx.fillText('LIVE', 222, 14);

            // Helper card renderer
            const drawCard = (x, y, cw, ch, label, val, valCol) => {
                ctx.fillStyle = '#111827';
                this.roundRect(ctx, x, y, cw, ch, 4);
                ctx.fill();
                ctx.fillStyle = '#94a3b8';
                ctx.font = '7.5px sans-serif';
                ctx.fillText(label, x + 6, y + 13);
                ctx.fillStyle = valCol || '#38bdf8';
                ctx.font = 'bold 11px sans-serif';
                ctx.fillText(val, x + 6, y + 28);
            };

            // 1. CHECK FOR REAL TENANT BUSINESS METRICS (MONITOR 1 - STRATEGIC RADAR)
            const realAgentData = (this.businessData && this.businessData[roleKey]) ? this.businessData[roleKey] : null;
            if (realAgentData && realAgentData.monitor1) {
                const m1 = realAgentData.monitor1;
                drawCard(10, 26, 112, 34, m1.kpi_label_1 || 'KPI 1', String(m1.kpi_value_1 || '0'), '#38bdf8');
                drawCard(128, 26, 118, 34, m1.kpi_label_2 || 'KPI 2', String(m1.kpi_value_2 || '0'), '#10b981');

                if (m1.chart_type === 'bar' && Array.isArray(m1.chart_data) && m1.chart_data.length > 0) {
                    const dataVals = m1.chart_data;
                    const maxVal = Math.max(...dataVals, 1);
                    const barCount = dataVals.length;
                    const availW = 216;
                    const barW = Math.max(8, Math.min(24, Math.floor(availW / barCount) - 6));
                    const startX = 20;

                    dataVals.forEach((v, idx) => {
                        const bh = Math.max(4, Math.min(48, Math.round((v / maxVal) * 44)));
                        const bx = startX + idx * (barW + 6);
                        const by = 126 - bh;
                        ctx.fillStyle = (idx === dataVals.length - 1) ? '#38bdf8' : '#10b981';
                        ctx.fillRect(bx, by, barW, bh);

                        if (m1.chart_labels && m1.chart_labels[idx]) {
                            ctx.fillStyle = '#64748b';
                            ctx.font = '7px sans-serif';
                            ctx.fillText(String(m1.chart_labels[idx]), bx, 136);
                        }
                    });
                } else {
                    const dataVals = (m1.chart_data && m1.chart_data.length > 0) ? m1.chart_data : [10, 14, 18, 15, 22, 28, 32];
                    const maxVal = Math.max(...dataVals, 1);
                    ctx.strokeStyle = '#38bdf8';
                    ctx.lineWidth = 2;
                    ctx.beginPath();
                    dataVals.forEach((v, idx) => {
                        const px = 20 + idx * Math.floor(210 / Math.max(1, dataVals.length - 1));
                        const py = 124 - Math.round((v / maxVal) * 42);
                        if (idx === 0) ctx.moveTo(px, py);
                        else ctx.lineTo(px, py);
                    });
                    ctx.stroke();
                }

                ctx.fillStyle = '#4ade80';
                ctx.font = '8px monospace';
                const sub1 = m1.kpi_sub_1 ? `${m1.kpi_sub_1}` : 'Tersinkronisasi DB';
                const sub2 = m1.kpi_sub_2 ? ` • ${m1.kpi_sub_2}` : '';
                ctx.fillText(`● ${sub1}${sub2}`.substring(0, 48), 12, 148);
                return;
            }

            // 17 BESPOKE, NON-TEMPLATED AGENT MONITORS (FALLBACK)
            if (roleKey === 'ceo') {
                const rev = (48.5 + Math.sin(elapsed * 0.4) * 0.4).toFixed(1);
                drawCard(10, 26, 112, 34, 'ARR RUN-RATE', `Rp ${rev}M`, '#38bdf8');
                drawCard(128, 26, 118, 34, 'VALUATION', 'Rp 450M (94% OKR)', '#10b981');

                // Smooth revenue curve with gradient area
                ctx.strokeStyle = '#38bdf8';
                ctx.lineWidth = 2;
                ctx.beginPath();
                for (let px = 14; px <= 242; px += 8) {
                    const py = 118 - Math.sin((px * 0.035) + elapsed * 1.4) * 16 - (px * 0.11);
                    if (px === 14) ctx.moveTo(px, py);
                    else ctx.lineTo(px, py);
                }
                ctx.stroke();

                // Target reference line
                ctx.strokeStyle = 'rgba(56, 189, 248, 0.25)';
                ctx.setLineDash([4, 4]);
                ctx.beginPath(); ctx.moveTo(14, 88); ctx.lineTo(242, 88); ctx.stroke();
                ctx.setLineDash([]);

                ctx.fillStyle = '#4ade80';
                ctx.font = '8.5px monospace';
                ctx.fillText('● Q4 OKR Execution: 94.2% • Series A Ready', 12, 148);

            } else if (roleKey === 'cfo') {
                drawCard(10, 26, 112, 34, 'CASH RUNWAY', '24.8 MONTHS', '#f59e0b');
                drawCard(128, 26, 118, 34, 'CASH RESERVE', 'Rp 8.42B (AUDITED)', '#10b981');

                // Waterfall Revenue vs Burn Bars (6 months)
                const revBars = [38, 42, 45, 52, 58, 64];
                const burnBars = [22, 24, 25, 26, 28, 27];
                for (let i = 0; i < 6; i++) {
                    const bx = 20 + i * 38;
                    ctx.fillStyle = '#10b981';
                    ctx.fillRect(bx, 130 - revBars[i], 12, revBars[i]);
                    ctx.fillStyle = '#ef4444';
                    ctx.fillRect(bx + 14, 130 - burnBars[i], 12, burnBars[i]);
                }
                ctx.fillStyle = '#fde68a';
                ctx.font = '8.5px monospace';
                ctx.fillText('● Tax Compliance PPh 21/23: Reconciled & Audited', 12, 148);

            } else if (roleKey === 'business') {
                drawCard(10, 26, 112, 34, 'LTV / CAC RATIO', '4.2x (HEALTHY)', '#a855f7');
                drawCard(128, 26, 118, 34, 'NET CHURN RATE', '0.8% / MONTH', '#34d399');

                // 3 Cohort Retention Decay Splines
                const colors = ['#a855f7', '#38bdf8', '#10b981'];
                colors.forEach((col, idx) => {
                    ctx.strokeStyle = col;
                    ctx.lineWidth = 1.6;
                    ctx.beginPath();
                    for (let px = 16; px <= 240; px += 10) {
                        const py = 76 + idx * 16 + (px * 0.12) + Math.sin(px * 0.04 + elapsed + idx) * 3;
                        if (px === 16) ctx.moveTo(px, py);
                        else ctx.lineTo(px, py);
                    }
                    ctx.stroke();
                });
                ctx.fillStyle = '#c084fc';
                ctx.font = '8.5px monospace';
                ctx.fillText('● Predictive ML Forecast: +24.6% Q1 Upsurge', 12, 148);

            } else if (roleKey === 'cmo') {
                drawCard(10, 26, 112, 34, 'BLENDED ROAS', '4.85x (+18% MoM)', '#c084fc');
                drawCard(128, 26, 118, 34, 'TOTAL AD SPEND', 'Rp 85.2M / MO', '#ec4899');

                // 4-Channel Attribution Stacked Bar Chart
                const channels = [
                    { name: 'Meta Ads', w: 96, col: '#3b82f6' },
                    { name: 'TikTok', w: 68, col: '#ec4899' },
                    { name: 'Google Ads', w: 42, col: '#f59e0b' },
                    { name: 'KOL / Influencer', w: 22, col: '#10b981' }
                ];
                let curX = 14;
                channels.forEach(ch => {
                    ctx.fillStyle = ch.col;
                    ctx.fillRect(curX, 86, ch.w, 18);
                    ctx.fillStyle = '#ffffff';
                    ctx.font = 'bold 7px sans-serif';
                    ctx.fillText(ch.name, curX + 4, 98);
                    curX += ch.w + 2;
                });
                ctx.fillStyle = '#e9d5ff';
                ctx.font = '8.5px monospace';
                ctx.fillText('● Campaign "Semarak UMKM": Viral Velocity 9.2/10', 12, 148);

            } else if (roleKey === 'marketing') {
                drawCard(10, 26, 112, 34, 'CLICK-THROUGH (CTR)', '3.14% (TOP 5%)', '#22d3ee');
                drawCard(128, 26, 118, 34, 'AVERAGE CPC', 'Rp 840 (SAVING)', '#38bdf8');

                // Real-time Live Impressions Pulse Wave
                ctx.strokeStyle = '#06b6d4';
                ctx.lineWidth = 1.8;
                ctx.beginPath();
                for (let px = 14; px <= 242; px += 6) {
                    const py = 108 + Math.sin(px * 0.08 + elapsed * 3) * 18 * Math.cos(px * 0.02);
                    if (px === 14) ctx.moveTo(px, py);
                    else ctx.lineTo(px, py);
                }
                ctx.stroke();

                // Live Scanning Pointer
                const scanX = 14 + (Math.floor(elapsed * 45) % 228);
                ctx.fillStyle = '#22d3ee';
                ctx.beginPath(); ctx.arc(scanX, 108, 3.5, 0, Math.PI * 2); ctx.fill();

                ctx.fillStyle = '#67e8f9';
                ctx.font = '8.5px monospace';
                ctx.fillText('● Meta Pixel & GA4 Conversions API: Live (0 Err)', 12, 148);

            } else if (roleKey === 'content') {
                drawCard(10, 26, 112, 34, 'ARTICLES PUBLISHED', '54 ARTICLES', '#34d399');
                drawCard(128, 26, 118, 34, 'AVG SEO AUDIT', '96 / 100 A+', '#10b981');

                // Keyword SERP Rank Distribution Bars
                const ranks = [
                    { label: 'Rank 1-3', count: 184, col: '#10b981', w: 140 },
                    { label: 'Rank 4-10', count: 96, col: '#38bdf8', w: 90 },
                    { label: 'Rank 11-20', count: 42, col: '#f59e0b', w: 50 }
                ];
                ranks.forEach((r, idx) => {
                    const ry = 72 + idx * 19;
                    ctx.fillStyle = '#94a3b8'; ctx.font = '7.5px sans-serif';
                    ctx.fillText(r.label, 14, ry + 11);
                    ctx.fillStyle = '#1e293b'; ctx.fillRect(68, ry, 160, 13);
                    ctx.fillStyle = r.col; ctx.fillRect(68, ry, r.w, 13);
                    ctx.fillStyle = '#ffffff'; ctx.font = 'bold 7px sans-serif';
                    ctx.fillText(String(r.count), 74, ry + 9.5);
                });
                ctx.fillStyle = '#a7f3d0';
                ctx.font = '8.5px monospace';
                ctx.fillText('● 184 High-Intent Keywords Indexed Google Rank #1', 12, 148);

            } else if (roleKey === 'social_media') {
                drawCard(10, 26, 112, 34, 'TOTAL ENGAGED REACH', '468.2K FANS', '#f472b6');
                drawCard(128, 26, 118, 34, 'ENGAGEMENT RATE', '6.2% (+2.4% VIRAL)', '#fb7185');

                // Viral Spike Wave with Peak Bubble
                ctx.strokeStyle = '#ec4899';
                ctx.lineWidth = 2;
                ctx.beginPath();
                for (let px = 14; px <= 242; px += 8) {
                    const distFromCenter = Math.abs(px - 140);
                    const spike = Math.exp(-distFromCenter * 0.04) * 35;
                    const py = 122 - spike - Math.sin(px * 0.05 + elapsed * 2) * 5;
                    if (px === 14) ctx.moveTo(px, py);
                    else ctx.lineTo(px, py);
                }
                ctx.stroke();

                ctx.fillStyle = '#fbcfe8';
                ctx.font = '8.5px monospace';
                ctx.fillText('● TikTok & IG Live: 2,840 Concurrent Viewers', 12, 148);

            } else if (roleKey === 'sales_director') {
                drawCard(10, 26, 112, 34, 'Q4 REVENUE TARGET', 'Rp 2.50B', '#38bdf8');
                drawCard(128, 26, 118, 34, 'CLOSED TO DATE', 'Rp 2.18B (87%)', '#10b981');

                // Enterprise Quota Gauge Bar
                ctx.fillStyle = '#1e293b'; ctx.fillRect(14, 84, 228, 22);
                ctx.fillStyle = '#10b981'; ctx.fillRect(14, 84, 228 * 0.87, 22);
                ctx.fillStyle = '#ffffff'; ctx.font = 'bold 8.5px sans-serif';
                ctx.fillText('87% QUOTA ATTAINED (TARGET REACH BY NOV)', 22, 98);

                ctx.fillStyle = '#6ee7b7';
                ctx.font = '8.5px monospace';
                ctx.fillText('● 8 Enterprise Master Service Agreements in Review', 12, 148);

            } else if (roleKey === 'sales') {
                drawCard(10, 26, 112, 34, 'QUALIFIED LEADS', '88 ACTIVE PIPELINE', '#34d399');
                drawCard(128, 26, 118, 34, 'CLOSING RATE', '38.4% (WON 54)', '#10b981');

                // 4-Stage Sales Funnel
                const stages = [
                    { name: '1. Inbound Leads: 142', w: 220, col: '#047857' },
                    { name: '2. Demo Booked: 88', w: 172, col: '#059669' },
                    { name: '3. Proposal Sent: 42', w: 126, col: '#10b981' },
                    { name: '4. Closed Won: 18', w: 84, col: '#34d399' }
                ];
                stages.forEach((st, idx) => {
                    const sx = (256 - st.w) / 2;
                    const sy = 68 + idx * 16;
                    ctx.fillStyle = st.col;
                    ctx.fillRect(sx, sy, st.w, 13);
                    ctx.fillStyle = '#ffffff';
                    ctx.font = 'bold 7px sans-serif';
                    ctx.fillText(st.name, sx + 6, sy + 9.5);
                });
                ctx.fillStyle = '#a7f3d0';
                ctx.font = '8.5px monospace';
                ctx.fillText('● 34 Automated WhatsApp Follow-ups Sent Today', 12, 148);

            } else if (roleKey === 'customer') {
                drawCard(10, 26, 112, 34, 'CSAT RATING', '98.6% (5 STAR)', '#38bdf8');
                drawCard(128, 26, 118, 34, 'FIRST RESPONSE SLA', '38 SECONDS', '#10b981');

                // Ticket Volume Histogram Bars
                const hours = [18, 24, 42, 68, 54, 38, 28, 14];
                hours.forEach((hVal, idx) => {
                    const bx = 22 + idx * 28;
                    ctx.fillStyle = '#0284c7';
                    ctx.fillRect(bx, 132 - hVal, 18, hVal);
                });
                ctx.fillStyle = '#7dd3fc';
                ctx.font = '8.5px monospace';
                ctx.fillText('● Omnichannel Inbox: WA, Tokopedia, Zendesk 0 Backlog', 12, 148);

            } else if (roleKey === 'coo') {
                drawCard(10, 26, 112, 34, 'SLA DISPATCH ON-TIME', '99.4% (+0.6%)', '#67e8f9');
                drawCard(128, 26, 118, 34, 'AVG DISPATCH TIME', '18 MINS / ORDER', '#06b6d4');

                // 4 Regional Hub Fulfillment Meters
                const hubs = [
                    { name: 'Hub Jakarta', rate: '99.8%', w: 140 },
                    { name: 'Hub Surabaya', rate: '99.2%', w: 135 },
                    { name: 'Hub Medan', rate: '98.9%', w: 128 },
                    { name: 'Hub Makassar', rate: '99.6%', w: 138 }
                ];
                hubs.forEach((hItem, idx) => {
                    const hy = 70 + idx * 17;
                    ctx.fillStyle = '#94a3b8'; ctx.font = '7.5px sans-serif';
                    ctx.fillText(hItem.name, 14, hy + 10);
                    ctx.fillStyle = '#1e293b'; ctx.fillRect(86, hy, 148, 11);
                    ctx.fillStyle = '#06b6d4'; ctx.fillRect(86, hy, hItem.w, 11);
                });
                ctx.fillStyle = '#a5f3fc';
                ctx.font = '8.5px monospace';
                ctx.fillText('● 24 Regional Logistics Hubs Operational & Synced', 12, 148);

            } else if (roleKey === 'inventory') {
                drawCard(10, 26, 112, 34, 'ACTIVE CATALOG SKUS', '14,820 SKUS', '#f59e0b');
                drawCard(128, 26, 118, 34, 'LOW-STOCK ALERTS', '2 CRITICAL RESTOCK', '#ef4444');

                // Stock SKU Levels Bar Chart with Safety Threshold
                for (let b = 0; b < 10; b++) {
                    const sh = 20 + Math.sin(b * 1.5 + elapsed) * 12 + (b % 3) * 10;
                    ctx.fillStyle = sh < 22 ? '#ef4444' : '#10b981';
                    ctx.fillRect(16 + b * 23, 130 - sh, 16, sh);
                }
                // Minimum Safety Dotted Line
                ctx.strokeStyle = '#ef4444';
                ctx.setLineDash([3, 3]);
                ctx.beginPath(); ctx.moveTo(14, 108); ctx.lineTo(242, 108); ctx.stroke();
                ctx.setLineDash([]);

                ctx.fillStyle = '#fde68a';
                ctx.font = '8.5px monospace';
                ctx.fillText('● Auto-PO Triggered for SKU-COOCA-902 & 908', 12, 148);

            } else if (roleKey === 'purchasing') {
                drawCard(10, 26, 112, 34, 'OPEN PURCHASE ORDERS', '18 ORDERS', '#a78bfa');
                drawCard(128, 26, 118, 34, 'TOTAL COST SAVINGS', 'Rp 164M NEGOTIATED', '#34d399');

                // Vendor Delivery Timeliness Curve
                ctx.strokeStyle = '#8b5cf6';
                ctx.lineWidth = 1.8;
                ctx.beginPath();
                for (let px = 14; px <= 242; px += 8) {
                    const py = 100 - Math.cos((px * 0.04) + elapsed * 1.8) * 14;
                    if (px === 14) ctx.moveTo(px, py);
                    else ctx.lineTo(px, py);
                }
                ctx.stroke();

                ctx.fillStyle = '#ddd6fe';
                ctx.font = '8.5px monospace';
                ctx.fillText('● 3 Vendor RFQs Auto-Evaluated & Approved', 12, 148);

            } else if (roleKey === 'marketplace') {
                drawCard(10, 26, 112, 34, 'SYNC LATENCY', '140 MS REALTIME', '#38bdf8');
                drawCard(128, 26, 118, 34, 'MULTI-STORE CATALOG', '42,890 ITEMS', '#10b981');

                // 4 Channels Status Grid
                const channels = [
                    { name: 'Shopee', status: '99.9% SYNC', col: '#ea580c' },
                    { name: 'Tokopedia', status: '100% LIVE', col: '#16a34a' },
                    { name: 'Lazada', status: '99.8% SYNC', col: '#2563eb' },
                    { name: 'TikTok Shop', status: '100% LIVE', col: '#db2777' }
                ];
                channels.forEach((c, idx) => {
                    const cx = 14 + (idx % 2) * 116;
                    const cy = 68 + Math.floor(idx / 2) * 32;
                    ctx.fillStyle = '#1e293b'; ctx.fillRect(cx, cy, 110, 28);
                    ctx.fillStyle = c.col; ctx.fillRect(cx, cy, 4, 28);
                    ctx.fillStyle = '#ffffff'; ctx.font = 'bold 8px sans-serif';
                    ctx.fillText(c.name, cx + 10, cy + 12);
                    ctx.fillStyle = '#10b981'; ctx.font = '7.5px monospace';
                    ctx.fillText(c.status, cx + 10, cy + 23);
                });

                ctx.fillStyle = '#67e8f9';
                ctx.font = '8.5px monospace';
                ctx.fillText('● Multi-tenant Stock Lock: 0 Oversell Incidents', 12, 148);

            } else if (roleKey === 'finance') {
                drawCard(10, 26, 112, 34, 'AUDITED RECONCILED', '100% (4,820 TX)', '#10b981');
                drawCard(128, 26, 118, 34, 'LEDGER BALANCE', 'Rp 14.8B (0 VAR)', '#34d399');

                // Twin Balanced Waves (Debit vs Credit Zero Variance)
                ctx.strokeStyle = '#10b981'; ctx.lineWidth = 1.6;
                ctx.beginPath();
                for (let px = 14; px <= 242; px += 8) {
                    const py = 100 + Math.sin(px * 0.05 + elapsed * 2) * 14;
                    if (px === 14) ctx.moveTo(px, py); else ctx.lineTo(px, py);
                }
                ctx.stroke();

                ctx.strokeStyle = '#38bdf8'; ctx.lineWidth = 1.4;
                ctx.beginPath();
                for (let px = 14; px <= 242; px += 8) {
                    const py = 100 - Math.sin(px * 0.05 + elapsed * 2) * 14;
                    if (px === 14) ctx.moveTo(px, py); else ctx.lineTo(px, py);
                }
                ctx.stroke();

                ctx.fillStyle = '#86efac';
                ctx.font = '8.5px monospace';
                ctx.fillText('● Auto-reconciliation Engine: 4,820 TXNs Verified', 12, 148);

            } else if (roleKey === 'reporting') {
                drawCard(10, 26, 112, 34, 'NET PROFIT MARGIN', '34.2% (+4.2% MoM)', '#38bdf8');
                drawCard(128, 26, 118, 34, 'PPN 11% ACCRUAL', 'Rp 284M READY', '#f59e0b');

                // Financial Statement Matrix Breakdown
                const rows = [
                    { label: 'Total Revenue', val: 'Rp 48.5M', col: '#10b981' },
                    { label: 'COGS Expense', val: 'Rp 18.2M', col: '#ef4444' },
                    { label: 'Gross Operating Profit', val: 'Rp 30.3M', col: '#38bdf8' }
                ];
                rows.forEach((rw, idx) => {
                    const ry = 70 + idx * 18;
                    ctx.fillStyle = '#1e293b'; ctx.fillRect(14, ry, 228, 15);
                    ctx.fillStyle = '#94a3b8'; ctx.font = '7.5px sans-serif';
                    ctx.fillText(rw.label, 20, ry + 10.5);
                    ctx.fillStyle = rw.col; ctx.font = 'bold 8px monospace';
                    ctx.fillText(rw.val, 176, ry + 10.5);
                });

                ctx.fillStyle = '#fde68a';
                ctx.font = '8.5px monospace';
                ctx.fillText('● E-Faktur CSV & Quarterly Financial Report Generated', 12, 148);

            } else if (roleKey === 'hr') {
                drawCard(10, 26, 112, 34, 'WORKFORCE ATTENDANCE', '99.2% (85/86)', '#fb7185');
                drawCard(128, 26, 118, 34, 'TOTAL HEADCOUNT', '86 EMPLOYEES', '#f472b6');

                // Department Breakdown Bars
                const depts = [
                    { name: 'Engineering', count: '34 emp', w: 130 },
                    { name: 'Operations', count: '22 emp', w: 90 },
                    { name: 'Growth & Sales', count: '18 emp', w: 70 },
                    { name: 'G&A / Finance', count: '12 emp', w: 50 }
                ];
                depts.forEach((dp, idx) => {
                    const dy = 68 + idx * 17;
                    ctx.fillStyle = '#94a3b8'; ctx.font = '7.5px sans-serif';
                    ctx.fillText(dp.name, 14, dy + 10);
                    ctx.fillStyle = '#1e293b'; ctx.fillRect(88, dy, 146, 11);
                    ctx.fillStyle = '#f43f5e'; ctx.fillRect(88, dy, dp.w, 11);
                });

                ctx.fillStyle = '#fbcfe8';
                ctx.font = '8.5px monospace';
                ctx.fillText('● BPJS Ketenagakerjaan & PPh 21 Payroll Calculated', 12, 148);

            } else {
                drawCard(10, 26, 112, 34, 'SYSTEM STATUS', '100% OPERATIONAL', '#10b981');
                drawCard(128, 26, 118, 34, 'TASKS PROCESSED', '1,842 COMPLETED', '#38bdf8');
                ctx.fillStyle = '#38bdf8';
                ctx.font = '8.5px monospace';
                ctx.fillText('● Standard AI Agent Cooca Process Active', 12, 148);
            }
        }

        renderSecondaryMonitorUI(ctx, roleKey, elapsed) {
            const w = 192;
            const h = 140;

            // VS Code / IntelliJ Dark Surface
            ctx.fillStyle = '#0d1117';
            ctx.fillRect(0, 0, w, h);

            // 1. CHECK FOR REAL TENANT BUSINESS DATA (MONITOR 2 - OPERATIONAL STREAM)
            const realAgentData = (this.businessData && this.businessData[roleKey]) ? this.businessData[roleKey] : null;
            if (realAgentData && realAgentData.monitor2 && Array.isArray(realAgentData.monitor2.items) && realAgentData.monitor2.items.length > 0) {
                const m2 = realAgentData.monitor2;

                // Tab bar
                ctx.fillStyle = '#161b22';
                ctx.fillRect(0, 0, w, 18);
                ctx.fillStyle = '#38bdf8';
                ctx.font = 'bold 8px monospace';
                ctx.fillText(m2.title || 'LIVE OPERATIONAL STREAM', 6, 12);

                // Active Tab bottom line
                ctx.fillStyle = '#10b981';
                ctx.fillRect(6, 16, 75, 2);

                // Table Header
                ctx.fillStyle = '#1e293b';
                ctx.fillRect(4, 22, w - 8, 12);
                ctx.fillStyle = '#64748b';
                ctx.font = 'bold 7px sans-serif';
                ctx.fillText('REF / ITEM', 8, 31);
                ctx.fillText('NILAI / DETAIL', 72, 31);
                ctx.fillText('STATUS', 148, 31);

                // Rows
                const items = m2.items.slice(0, 5);
                items.forEach((it, idx) => {
                    const ry = 36 + idx * 16;
                    ctx.fillStyle = idx % 2 === 0 ? '#0d1117' : '#111827';
                    ctx.fillRect(4, ry, w - 8, 15);

                    ctx.fillStyle = '#f8fafc';
                    ctx.font = '7px monospace';
                    ctx.fillText(String(it.col1 || '').substring(0, 13), 8, ry + 11);

                    ctx.fillStyle = '#94a3b8';
                    ctx.font = '7px sans-serif';
                    ctx.fillText(String(it.col2 || '').substring(0, 14), 72, ry + 11);

                    const st = String(it.col3 || 'OK').toUpperCase();
                    let stCol = '#10b981';
                    if (st.includes('KRITIS') || st.includes('OVERDUE') || st.includes('VOID') || st.includes('HIGH')) {
                        stCol = '#ef4444';
                    } else if (st.includes('MENIPIS') || st.includes('UNPAID') || st.includes('DRAFT') || st.includes('WAITING') || st.includes('ROP')) {
                        stCol = '#f59e0b';
                    } else if (st.includes('HADIR') || st.includes('LUNAS') || st.includes('AMAN') || st.includes('SELESAI')) {
                        stCol = '#10b981';
                    } else {
                        stCol = '#38bdf8';
                    }

                    ctx.fillStyle = stCol;
                    ctx.font = 'bold 7px monospace';
                    ctx.fillText(st.substring(0, 9), 148, ry + 11);
                });

                // Status footer
                ctx.fillStyle = '#161b22';
                ctx.fillRect(0, 122, w, 18);
                ctx.fillStyle = '#10b981';
                ctx.font = 'bold 8px monospace';
                const cursor = Math.sin(elapsed * 4) > 0 ? '▌' : ' ';
                ctx.fillText(`● LIVE DB FEED ${cursor}`, 6, 134);
                return;
            }

            // 17 BESPOKE CODE & DOCUMENT REPOSITORIES (FALLBACK)
            const configs = {
                ceo: {
                    file: 'ceo_orchestrator.ts',
                    fileColor: '#38bdf8',
                    cli: 'cooca-ceo --exec-okr',
                    lines: [
                        { text: 'import { aiCore } from "@cooca/core";', color: '#c084fc' },
                        { text: 'const okr = await OKR.get("2026_Q4");', color: '#38bdf8' },
                        { text: 'await aiCore.syncRoadmap(okr);', color: '#f8fafc' },
                        { text: 'const health = await check17Agents();', color: '#34d399' },
                        { text: 'if (health.score >= 0.98) {', color: '#f59e0b' },
                        { text: '  log("Series A execution on track");', color: '#f8fafc' },
                        { text: '  dispatchBoardDeck({ verified: true });', color: '#38bdf8' },
                        { text: '}', color: '#f59e0b' }
                    ]
                },
                cfo: {
                    file: 'cashflow_model.py',
                    fileColor: '#f59e0b',
                    cli: 'cfo-audit --reconcile',
                    lines: [
                        { text: 'def calculate_runway(cash, burn):', color: '#c084fc' },
                        { text: '    runway_mo = cash / burn', color: '#38bdf8' },
                        { text: '    ebitda = calculate_ebitda()', color: '#f8fafc' },
                        { text: '    assert ebitda.margin >= 0.34', color: '#34d399' },
                        { text: '    reconcile_bank_feeds(auto=True)', color: '#f59e0b' },
                        { text: '    tax_vat = compute_ppn_11()', color: '#f8fafc' },
                        { text: '    return {"runway": runway_mo}', color: '#38bdf8' },
                        { text: 'audit_trail.commit(status="CLEAN")', color: '#34d399' }
                    ]
                },
                business: {
                    file: 'cohort_analytics.sql',
                    fileColor: '#fb923c',
                    cli: 'bq-query --sync-bi',
                    lines: [
                        { text: 'SELECT cohort_month,', color: '#c084fc' },
                        { text: '  COUNT(tenant_id) AS total_tenants,', color: '#38bdf8' },
                        { text: '  ROUND(SUM(mrr), 2) AS mrr_sum,', color: '#f8fafc' },
                        { text: '  AVG(retention_90d) AS nrr_rate', color: '#34d399' },
                        { text: 'FROM `cooca_dw.tenants`', color: '#f59e0b' },
                        { text: 'WHERE status = "ACTIVE"', color: '#f8fafc' },
                        { text: 'GROUP BY cohort_month', color: '#c084fc' },
                        { text: 'ORDER BY cohort_month DESC;', color: '#38bdf8' }
                    ]
                },
                cmo: {
                    file: 'growth_engine.ts',
                    fileColor: '#c084fc',
                    cli: 'growth-opt --bidding',
                    lines: [
                        { text: 'export async function optimizeAds() {', color: '#c084fc' },
                        { text: '  const roas = await meta.getROAS();', color: '#38bdf8' },
                        { text: '  if (roas < TARGET_ROAS) {', color: '#f59e0b' },
                        { text: '    await budget.reallocate("tiktok");', color: '#34d399' },
                        { text: '  }', color: '#f59e0b' },
                        { text: '  await creativeEngine.rotateAssets();', color: '#f8fafc' },
                        { text: '  await pushKOLBriefs({ slots: 12 });', color: '#38bdf8' },
                        { text: '}', color: '#c084fc' }
                    ]
                },
                marketing: {
                    file: 'meta_campaigns.ts',
                    fileColor: '#22d3ee',
                    cli: 'meta-ads --stream-cpc',
                    lines: [
                        { text: 'const camp = await metaAds.create({', color: '#c084fc' },
                        { text: '  name: "UMKM_Booster_Q4",', color: '#f59e0b' },
                        { text: '  strategy: "LOWEST_COST",', color: '#38bdf8' },
                        { text: '  cpm_cap: 850,', color: '#34d399' },
                        { text: '  conversions_api: "active"', color: '#f8fafc' },
                        { text: '});', color: '#c084fc' },
                        { text: 'await ga4.verifyAttribution(camp.id);', color: '#38bdf8' },
                        { text: 'log("Meta campaign live 0 errors");', color: '#34d399' }
                    ]
                },
                content: {
                    file: 'seo_cluster.md',
                    fileColor: '#34d399',
                    cli: 'cooca-seo --rank-watch',
                    lines: [
                        { text: '# Cluster: ERP Bengkel & Kasir', color: '#34d399' },
                        { text: '- Keyword: "software bengkel motor"', color: '#38bdf8' },
                        { text: '- Volume: 18.2K / mo (KD: 24)', color: '#f59e0b' },
                        { text: '- Schema: Product + HowTo JSON-LD', color: '#f8fafc' },
                        { text: '### Outline & Meta Description', color: '#c084fc' },
                        { text: '- Status: Google SERP Rank #1', color: '#34d399' },
                        { text: '- AI Copy Pipeline: 12 Articles Live', color: '#f8fafc' },
                        { text: '--- Verified by Content Agent ---', color: '#475569' }
                    ]
                },
                social_media: {
                    file: 'viral_schedule.json',
                    fileColor: '#f472b6',
                    cli: 'tiktok-bot --feed-listen',
                    lines: [
                        { text: '{', color: '#f8fafc' },
                        { text: '  "channel": "tiktok_official",', color: '#f472b6' },
                        { text: '  "reel_queue": [', color: '#38bdf8' },
                        { text: '    { "id": 892, "hook": "Buka Cabang!" },', color: '#f59e0b' },
                        { text: '    { "time": "19:00", "sound": "viral" }', color: '#34d399' },
                        { text: '  ],', color: '#38bdf8' },
                        { text: '  "auto_dm_webhook": true', color: '#c084fc' },
                        { text: '}', color: '#f8fafc' }
                    ]
                },
                sales_director: {
                    file: 'enterprise_deals.ts',
                    fileColor: '#10b981',
                    cli: 'crm-cli --deal-forecast',
                    lines: [
                        { text: 'const deals = await CRM.stage("LEGAL");', color: '#c084fc' },
                        { text: 'for (const deal of deals) {', color: '#38bdf8' },
                        { text: '  if (deal.value > 100_000_000) {', color: '#f59e0b' },
                        { text: '    await scheduleClosingCall(deal);', color: '#34d399' },
                        { text: '    await notifyLegalDirector(deal);', color: '#f8fafc' },
                        { text: '  }', color: '#f59e0b' },
                        { text: '}', color: '#38bdf8' },
                        { text: 'await CRM.refreshQuotaAttainment();', color: '#10b981' }
                    ]
                },
                sales: {
                    file: 'lead_scorer.py',
                    fileColor: '#facc15',
                    cli: 'wa-crm-bot --auto-dial',
                    lines: [
                        { text: 'def score_inbound_lead(lead):', color: '#c084fc' },
                        { text: '    score = lead.branches * 15', color: '#38bdf8' },
                        { text: '    if lead.responded_in_5m: score += 40', color: '#34d399' },
                        { text: '    if lead.has_pos_system: score += 20', color: '#f8fafc' },
                        { text: '    if score >= 75:', color: '#f59e0b' },
                        { text: '        trigger_wa_demo_invite(lead)', color: '#34d399' },
                        { text: '    return score', color: '#38bdf8' },
                        { text: 'log(f"Lead {lead.id} auto-scheduled")', color: '#f8fafc' }
                    ]
                },
                customer: {
                    file: 'ticket_triage.ts',
                    fileColor: '#38bdf8',
                    cli: 'zendesk --sla-monitor',
                    lines: [
                        { text: 'onTicketCreated(async (ticket) => {', color: '#c084fc' },
                        { text: '  const intent = await nlp.classify(ticket);', color: '#38bdf8' },
                        { text: '  if (intent === "PRINTER_SETUP") {', color: '#f59e0b' },
                        { text: '    await bot.replyKnowledge(ticket);', color: '#34d399' },
                        { text: '    return resolveTicket(ticket.id);', color: '#f8fafc' },
                        { text: '  }', color: '#f59e0b' },
                        { text: '  assignAgent(ticket, "L2_SUPPORT");', color: '#38bdf8' },
                        { text: '});', color: '#c084fc' }
                    ]
                },
                coo: {
                    file: 'supply_chain.ts',
                    fileColor: '#2dd4bf',
                    cli: 'fleet-core --hub-status',
                    lines: [
                        { text: 'export async function balanceHubs() {', color: '#c084fc' },
                        { text: '  const hubs = await HubNetwork.all();', color: '#38bdf8' },
                        { text: '  for (const hub of hubs) {', color: '#f8fafc' },
                        { text: '    if (hub.loadRatio > 0.85) {', color: '#f59e0b' },
                        { text: '      await routeToAlternate(hub);', color: '#34d399' },
                        { text: '    }', color: '#f59e0b' },
                        { text: '  }', color: '#f8fafc' },
                        { text: '}', color: '#c084fc' }
                    ]
                },
                inventory: {
                    file: 'sku_watcher.py',
                    fileColor: '#fbbf24',
                    cli: 'stock-watcher --scan-rfid',
                    lines: [
                        { text: 'def check_reorder_point(sku):', color: '#c084fc' },
                        { text: '    velocity = sku.get_velocity_30d()', color: '#38bdf8' },
                        { text: '    safety_stock = velocity * 7', color: '#f8fafc' },
                        { text: '    if sku.qty <= safety_stock:', color: '#f59e0b' },
                        { text: '        po = generate_po(sku, sku.eoq)', color: '#ef4444' },
                        { text: '        push_procurement(po)', color: '#34d399' },
                        { text: '    return sku.qty', color: '#38bdf8' },
                        { text: 'audit_stock_bins(accuracy=0.998)', color: '#34d399' }
                    ]
                },
                purchasing: {
                    file: 'rfq_comparator.ts',
                    fileColor: '#a78bfa',
                    cli: 'vendor-portal --edi-sync',
                    lines: [
                        { text: 'const rfq = await RFQ.find(poNum);', color: '#c084fc' },
                        { text: 'const bestVendor = rfq.bids.reduce((a, b) => {', color: '#38bdf8' },
                        { text: '  return (a.price * a.days < b.price * b.days)', color: '#f8fafc' },
                        { text: '    ? a : b;', color: '#34d399' },
                        { text: '});', color: '#c084fc' },
                        { text: 'await PurchaseOrder.issue(bestVendor);', color: '#f59e0b' },
                        { text: 'log("PO issued with 14% discount");', color: '#34d399' },
                        { text: 'await notifyWarehouse(bestVendor.eta);', color: '#38bdf8' }
                    ]
                },
                marketplace: {
                    file: 'channel_sync.go',
                    fileColor: '#38bdf8',
                    cli: 'mp-sync --listen-webhooks',
                    lines: [
                        { text: 'func SyncStock(sku string, qty int) {', color: '#c084fc' },
                        { text: '    lock := Redis.Lock("sku:" + sku)', color: '#38bdf8' },
                        { text: '    defer lock.Unlock()', color: '#f59e0b' },
                        { text: '    shopee.UpdateInventory(sku, qty)', color: '#34d399' },
                        { text: '    tokopedia.UpdateInventory(sku, qty)', color: '#f8fafc' },
                        { text: '    tiktokShop.UpdateInventory(sku, qty)', color: '#34d399' },
                        { text: '    lazada.UpdateInventory(sku, qty)', color: '#38bdf8' },
                        { text: '}', color: '#c084fc' }
                    ]
                },
                finance: {
                    file: 'double_entry.py',
                    fileColor: '#34d399',
                    cli: 'ledger --verify-integrity',
                    lines: [
                        { text: 'def post_journal_entry(tx):', color: '#c084fc' },
                        { text: '    assert tx.debit == tx.credit, "Imbalance!"', color: '#ef4444' },
                        { text: '    ledger.insert(tx.debit_acc, tx.amount)', color: '#38bdf8' },
                        { text: '    ledger.insert(tx.credit_acc, -tx.amount)', color: '#f8fafc' },
                        { text: '    sha = compute_sha256(tx)', color: '#34d399' },
                        { text: '    audit_trail.commit(sha)', color: '#f59e0b' },
                        { text: '    return {"status": "BALANCED"}', color: '#38bdf8' },
                        { text: 'verify_daily_closing_balance()', color: '#34d399' }
                    ]
                },
                reporting: {
                    file: 'tax_compliance.sql',
                    fileColor: '#f59e0b',
                    cli: 'djp-efaktur --compile-csv',
                    lines: [
                        { text: 'SELECT tax_period,', color: '#c084fc' },
                        { text: '  SUM(dpp_sales) AS dpp,', color: '#38bdf8' },
                        { text: '  SUM(dpp_sales * 0.11) AS ppn_keluaran,', color: '#f59e0b' },
                        { text: '  SUM(ppn_masukan) AS kredit_pajak,', color: '#34d399' },
                        { text: '  SUM(dpp_sales * 0.11) - SUM(ppn_masukan) AS sisa', color: '#f8fafc' },
                        { text: 'FROM tax.faktur_pajak_2026', color: '#c084fc' },
                        { text: 'GROUP BY tax_period;', color: '#38bdf8' },
                        { text: '-- Verified for DJP e-Faktur filing --', color: '#475569' }
                    ]
                },
                hr: {
                    file: 'payroll_calc.ts',
                    fileColor: '#fb7185',
                    cli: 'payroll --run-disburse',
                    lines: [
                        { text: 'export function calcPayroll(emp: Employee) {', color: '#c084fc' },
                        { text: '  const pph21 = taxEngine.pph21(emp.salary);', color: '#38bdf8' },
                        { text: '  const bpjs = emp.salary * 0.04;', color: '#f59e0b' },
                        { text: '  const netPay = emp.salary - pph21 - bpjs;', color: '#34d399' },
                        { text: '  return { net: netPay, pph: pph21 };', color: '#f8fafc' },
                        { text: '}', color: '#c084fc' },
                        { text: 'await bankTransfer.scheduleBatch();', color: '#38bdf8' },
                        { text: 'log("Payroll disbursement ready 86 emp");', color: '#34d399' }
                    ]
                }
            };

            const cfg = configs[roleKey] || {
                file: `${roleKey}_worker.ts`,
                fileColor: '#38bdf8',
                cli: `cooca-agent --live`,
                lines: [
                    { text: 'import { aiCore } from "@cooca";', color: '#c084fc' },
                    { text: 'export async function exec() {', color: '#38bdf8' },
                    { text: '  const task = await pollQueue();', color: '#f8fafc' },
                    { text: '  if (!task.verified) return;', color: '#f59e0b' },
                    { text: '  await aiCore.dispatch({', color: '#34d399' },
                    { text: '    action: "AUTO_TASK", status: "OK"', color: '#38bdf8' },
                    { text: '  });', color: '#f8fafc' },
                    { text: '}', color: '#38bdf8' }
                ]
            };

            // Tab bar
            ctx.fillStyle = '#161b22';
            ctx.fillRect(0, 0, w, 18);
            ctx.fillStyle = cfg.fileColor;
            ctx.font = 'bold 8.5px monospace';
            ctx.fillText(cfg.file, 8, 12);

            // Active Tab bottom line
            ctx.fillStyle = cfg.fileColor;
            ctx.fillRect(6, 16, 75, 2);

            // Scrolling code lines
            const scrollOffset = (Math.floor(elapsed * 1.2) % cfg.lines.length);

            for (let i = 0; i < 7; i++) {
                const lineIdx = (i + scrollOffset) % cfg.lines.length;
                const line = cfg.lines[lineIdx];
                const y = 32 + i * 13;

                // Line number
                ctx.fillStyle = '#475569';
                ctx.font = '7.5px monospace';
                ctx.fillText(String(i + 1), 6, y);

                // Code text
                ctx.fillStyle = line.color;
                ctx.font = '7.5px monospace';
                ctx.fillText(line.text, 22, y);
            }

            // Terminal status line with blinking cursor
            ctx.fillStyle = '#161b22';
            ctx.fillRect(0, 122, w, 18);
            ctx.fillStyle = '#10b981';
            ctx.font = 'bold 8px monospace';
            const cursor = Math.sin(elapsed * 4) > 0 ? '▌' : ' ';
            ctx.fillText(`$ ${cfg.cli} ${cursor}`, 6, 134);
        }

        updateLiveMonitorScreens(elapsed, delta) {
            if (!this.liveMonitors || this.liveMonitors.length === 0) return;

            // Throttle to 12-15 FPS to ensure ultra-smooth 60 FPS WebGL scene performance
            this.liveMonitors.forEach((m) => {
                if (elapsed - (m.lastUpdate || 0) < 0.075) return;
                m.lastUpdate = elapsed;

                if (m.type === 'primary') {
                    this.renderPrimaryMonitorUI(m.ctx, m.roleKey, m.team, elapsed);
                } else {
                    this.renderSecondaryMonitorUI(m.ctx, m.roleKey, elapsed);
                }
                m.texture.needsUpdate = true;
            });
        }

        createSimsAgentWorkstation(roleKey, agentData, pos) {
            const rot = pos.rot || 0;
            const group = new THREE.Group();
            group.position.set(pos.x, pos.y, pos.z);
            group.rotation.y = rot;
            group.userData = { roleKey, agentData, team: pos.team, isLead: !!pos.isLead, yLevel: 0, type: 'desk' };

            // Premium Executive Desk Surface (realistic 0.74m height, matching room floor wood tone)
            const deskMat = new THREE.MeshStandardMaterial({
                color: pos.isLead ? 0x271e18 : (pos.team === 'marketing' ? 0xe2d7c5 : (pos.team === 'operations' ? 0x334155 : 0xf8fafc)),
                roughness: 0.25,
                metalness: 0.08
            });
            const legMat = new THREE.MeshStandardMaterial({ color: 0x18181b, metalness: 0.85, roughness: 0.15 });

            // Desk Top: width 2.0m, thickness 0.04m, depth 0.95m, top surface at y = 0.74m (center at 0.72m)
            const deskTop = new THREE.Mesh(new THREE.BoxGeometry(2.0, 0.04, 0.95), deskMat);
            deskTop.position.set(0, 0.72, 0);
            deskTop.castShadow = true;
            deskTop.receiveShadow = true;
            group.add(deskTop);

            // Chamfer Bevel Rim
            const trimMat = new THREE.MeshStandardMaterial({ color: 0x475569, metalness: 0.9, roughness: 0.2 });
            const frontTrim = new THREE.Mesh(new THREE.BoxGeometry(2.02, 0.02, 0.03), trimMat);
            frontTrim.position.set(0, 0.73, 0.48);
            group.add(frontTrim);

            // Steel Desk Legs (height 0.70m, center 0.35m)
            [[-0.9, 0.35, -0.38], [0.9, 0.35, -0.38], [-0.9, 0.35, 0.38], [0.9, 0.35, 0.38]].forEach(c => {
                const leg = new THREE.Mesh(new THREE.CylinderGeometry(0.025, 0.02, 0.70, 16), legMat);
                leg.position.set(c[0], c[1], c[2]);
                leg.castShadow = true;
                group.add(leg);
            });

            // 1. Full-Desk Felt Deskpad / Mousepad (resting right on top of desk at 0.74m)
            const deskPadMat = new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.85 });
            const deskPad = new THREE.Mesh(new THREE.BoxGeometry(1.4, 0.005, 0.52), deskPadMat);
            deskPad.position.set(0.05, 0.743, 0.05);
            deskPad.receiveShadow = true;
            group.add(deskPad);

            // 2. Mechanical Keyboard with glowing subtle keycaps
            const kbMat = new THREE.MeshStandardMaterial({ color: 0x0f172a, roughness: 0.3 });
            const keyboard = new THREE.Mesh(new THREE.BoxGeometry(0.50, 0.016, 0.16), kbMat);
            keyboard.position.set(-0.05, 0.751, 0.12);
            group.add(keyboard);

            // Keyboard Spacebar & Accent key
            const spacebar = new THREE.Mesh(new THREE.BoxGeometry(0.18, 0.006, 0.03), new THREE.MeshStandardMaterial({ color: 0x38bdf8, roughness: 0.2 }));
            spacebar.position.set(-0.05, 0.762, 0.16);
            group.add(spacebar);

            // 3. Ergonomic Optical Mouse with subtle LED glow
            const mouseMat = new THREE.MeshStandardMaterial({ color: 0x18181b, roughness: 0.2 });
            const mouse = new THREE.Mesh(new THREE.BoxGeometry(0.08, 0.022, 0.13), mouseMat);
            mouse.position.set(0.40, 0.754, 0.12);
            group.add(mouse);

            // 4. Ceramic Coffee Mug with Liquid Coffee
            const mugMat = new THREE.MeshStandardMaterial({ color: 0xf8fafc, roughness: 0.15 });
            const mug = new THREE.Mesh(new THREE.CylinderGeometry(0.045, 0.038, 0.095, 16), mugMat);
            mug.position.set(-0.55, 0.788, 0.10);
            mug.castShadow = true;
            group.add(mug);

            const coffeeLiquid = new THREE.Mesh(new THREE.CylinderGeometry(0.04, 0.04, 0.008, 16), new THREE.MeshStandardMaterial({ color: 0x271e18, roughness: 0.3 }));
            coffeeLiquid.position.set(-0.55, 0.825, 0.10);
            group.add(coffeeLiquid);
            group.userData.coffeeMug = mug;

            // 5. Aluminum Smartphone Stand & Smartphone
            const phoneStand = new THREE.Mesh(new THREE.BoxGeometry(0.08, 0.06, 0.06), legMat);
            phoneStand.position.set(0.60, 0.76, -0.15);
            group.add(phoneStand);

            const phone = new THREE.Mesh(new THREE.BoxGeometry(0.07, 0.13, 0.008), new THREE.MeshStandardMaterial({ color: 0x090d16, emissive: 0x38bdf8, emissiveIntensity: 0.4 }));
            phone.rotation.x = -Math.PI / 6;
            phone.position.set(0.60, 0.80, -0.14);
            group.add(phone);

            // ==========================================
            // DUAL WORKING MONITORS (REALISTIC 27" & 24" 16:9 PROPORTIONS)
            // ==========================================
            // A. Primary 27" Working Monitor (Screen center at 1.05m, eye level)
            const primaryCanvas = this.createPrimaryMonitorCanvas(roleKey, pos.team);
            const primaryTexture = new THREE.CanvasTexture(primaryCanvas);
            const primaryMat = new THREE.MeshStandardMaterial({
                map: primaryTexture,
                emissiveMap: primaryTexture,
                emissive: 0xffffff,
                emissiveIntensity: 0.95,
                roughness: 0.2
            });

            // Primary Monitor Frame: 0.64m x 0.38m (True 27" 16:9 scale)
            const primaryFrame = new THREE.Mesh(new THREE.BoxGeometry(0.64, 0.38, 0.025), new THREE.MeshStandardMaterial({ color: 0x0f172a, roughness: 0.3 }));
            primaryFrame.position.set(-0.18, 1.05, -0.22);
            primaryFrame.rotation.y = 0.04;
            group.add(primaryFrame);

            const primaryScreen = new THREE.Mesh(new THREE.PlaneGeometry(0.61, 0.35), primaryMat);
            primaryScreen.position.set(-0.18, 1.05, -0.207);
            primaryScreen.rotation.y = 0.04;
            group.add(primaryScreen);
            group.userData.monitor = primaryScreen;

            // C. Ergonomic Slim LED Task Lightbar (ScreenBar mounted on Primary Monitor)
            const lightbarMat = new THREE.MeshStandardMaterial({
                color: 0xffedd5,
                emissive: 0xffedd5,
                emissiveIntensity: this.isNight ? 2.2 : 0.35,
                roughness: 0.15
            });
            this.deskLampMaterials.push(lightbarMat);

            // Lightbar mount clamp on top of primary monitor frame
            const lightbarClamp = new THREE.Mesh(new THREE.BoxGeometry(0.04, 0.05, 0.03), legMat);
            lightbarClamp.position.set(-0.18, 1.25, -0.22);
            lightbarClamp.rotation.y = 0.04;
            group.add(lightbarClamp);

            // Aluminum horizontal cylinder bar
            const lightbarBody = new THREE.Mesh(new THREE.CylinderGeometry(0.009, 0.009, 0.44, 16), legMat);
            lightbarBody.rotation.z = Math.PI / 2;
            lightbarBody.position.set(-0.18, 1.27, -0.195);
            lightbarBody.rotation.y = 0.04;
            group.add(lightbarBody);

            // Downward-facing LED diffuser strip
            const lightbarDiffuser = new THREE.Mesh(new THREE.BoxGeometry(0.40, 0.005, 0.010), lightbarMat);
            lightbarDiffuser.position.set(-0.18, 1.265, -0.195);
            lightbarDiffuser.rotation.y = 0.04;
            group.add(lightbarDiffuser);

            // Primary Stand
            const primaryPole = new THREE.Mesh(new THREE.CylinderGeometry(0.016, 0.016, 0.28, 16), legMat);
            primaryPole.position.set(-0.18, 0.88, -0.22);
            group.add(primaryPole);

            const primaryBase = new THREE.Mesh(new THREE.BoxGeometry(0.24, 0.012, 0.16), legMat);
            primaryBase.position.set(-0.18, 0.746, -0.22);
            group.add(primaryBase);

            // B. Secondary 24" Working Monitor (Angled inward rot = -0.38)
            const secCanvas = this.createSecondaryMonitorCanvas(roleKey);
            const secTexture = new THREE.CanvasTexture(secCanvas);
            const secMat = new THREE.MeshStandardMaterial({
                map: secTexture,
                emissiveMap: secTexture,
                emissive: 0xffffff,
                emissiveIntensity: 0.90,
                roughness: 0.2
            });

            // Secondary Frame: 0.55m x 0.34m (True 24" 16:9 scale)
            const secFrame = new THREE.Mesh(new THREE.BoxGeometry(0.55, 0.34, 0.025), new THREE.MeshStandardMaterial({ color: 0x0f172a, roughness: 0.3 }));
            secFrame.position.set(0.42, 1.03, -0.16);
            secFrame.rotation.y = -0.38;
            group.add(secFrame);

            const secScreen = new THREE.Mesh(new THREE.PlaneGeometry(0.52, 0.31), secMat);
            secScreen.position.set(0.42, 1.03, -0.147);
            secScreen.rotation.y = -0.38;
            group.add(secScreen);

            // Secondary Stand
            const secPole = new THREE.Mesh(new THREE.CylinderGeometry(0.014, 0.014, 0.28, 16), legMat);
            secPole.position.set(0.42, 0.88, -0.16);
            group.add(secPole);

            const secBase = new THREE.Mesh(new THREE.BoxGeometry(0.20, 0.012, 0.15), legMat);
            secBase.position.set(0.42, 0.746, -0.16);
            secBase.rotation.y = -0.38;
            group.add(secBase);

            // Register both live monitors into tracking array
            this.liveMonitors.push({
                roleKey,
                team: pos.team,
                type: 'primary',
                canvas: primaryCanvas,
                ctx: primaryCanvas.getContext('2d'),
                texture: primaryTexture,
                lastUpdate: 0
            });

            this.liveMonitors.push({
                roleKey,
                team: pos.team,
                type: 'secondary',
                canvas: secCanvas,
                ctx: secCanvas.getContext('2d'),
                texture: secTexture,
                lastUpdate: 0
            });

            // Ergonomic Mesh Task Chair (Realistic seat cushion at 0.46m)
            const chairGroup = new THREE.Group();
            chairGroup.position.set(0, 0, 0.60);
            const chairMat = new THREE.MeshStandardMaterial({ color: 0x18181b, roughness: 0.45, metalness: 0.1 });
            const meshMat = new THREE.MeshStandardMaterial({ color: 0x27272a, roughness: 0.7 });

            const chairSeat = new THREE.Mesh(new THREE.BoxGeometry(0.50, 0.06, 0.48), meshMat);
            chairSeat.position.y = 0.46;
            chairSeat.castShadow = true;
            chairGroup.add(chairSeat);

            const chairBack = new THREE.Mesh(new THREE.BoxGeometry(0.44, 0.52, 0.05), chairMat);
            chairBack.position.set(0, 0.75, 0.22);
            chairBack.castShadow = true;
            chairGroup.add(chairBack);

            // Chrome 5-Star Caster Base (height 0.42m)
            const chairBase = new THREE.Mesh(new THREE.CylinderGeometry(0.03, 0.03, 0.42, 16), legMat);
            chairBase.position.set(0, 0.21, 0);
            chairGroup.add(chairBase);

            // 5-Star Spider Legs
            for (let i = 0; i < 5; i++) {
                const legAngle = i * (Math.PI * 2 / 5);
                const starLeg = new THREE.Mesh(new THREE.BoxGeometry(0.04, 0.02, 0.25), legMat);
                starLeg.position.set(Math.sin(legAngle) * 0.12, 0.03, Math.cos(legAngle) * 0.12);
                starLeg.rotation.y = legAngle;
                chairGroup.add(starLeg);
            }

            group.add(chairGroup);

            // Raycast Collider for Workstation Desk (User can click desk directly to assign task)
            const deskHitBox = new THREE.Mesh(
                new THREE.BoxGeometry(2.1, 1.2, 1.4),
                new THREE.MeshBasicMaterial({ visible: false })
            );
            deskHitBox.position.set(0, 0.65, 0.1);
            deskHitBox.userData = { roleKey, agentData, type: 'desk', workstation: group };
            group.add(deskHitBox);
            this.interactiveObjects.push(deskHitBox);

            // SIMS HUMANOID CHARACTER MODEL (Attached directly to scene in world coordinates)
            const charWorldX = pos.x + Math.sin(rot) * 0.60;
            const charWorldZ = pos.z + Math.cos(rot) * 0.60;
            const charWorldRot = rot + Math.PI;

            // Register this agent's private desk chair in ChairRegistry (100% exclusive to this agent, seatHeight 0.46)
            this.chairRegistry.set('desk_' + roleKey, {
                id: 'desk_' + roleKey,
                facility: 'desk',
                pos: new THREE.Vector3(charWorldX, 0, charWorldZ),
                rot: charWorldRot,
                approachNode: this.navNodes[this.getDeskApproachNodeKey(roleKey)] ? this.navNodes[this.getDeskApproachNodeKey(roleKey)].clone() : new THREE.Vector3(pos.x, 0, pos.z),
                seatHeight: 0.46,
                occupiedBy: roleKey,
                roleKey: roleKey
            });

            const simsAgent = this.createSimsHumanoidCharacter(
                roleKey,
                agentData,
                pos,
                { x: charWorldX, y: 0, z: charWorldZ, rot: charWorldRot },
                primaryScreen,
                group
            );
            simsAgent.position.set(charWorldX, 0, charWorldZ);
            simsAgent.rotation.y = charWorldRot;
            this.scene.add(simsAgent);

            return group;
        }

        createSimsHumanoidCharacter(roleKey, agentData, pos, worldTransform, monitorMesh, workstationGroup) {
            const charGroup = new THREE.Group();
            charGroup.userData = { roleKey, agentData, yLevel: 0, type: 'agent' };

            // Sub-group: Body pivot allows raising and lowering hips naturally when sitting down vs standing
            const bodyPivot = new THREE.Group();
            bodyPivot.position.set(0, 0, 0);
            charGroup.add(bodyPivot);

            // 1. Natural Skin Tones with Soft Subsurface PBR Feel
            const skinTones = [0xf5d0b5, 0xe0ac69, 0xc68642, 0x8d5524, 0xfbe0c8];
            let skinColor = skinTones[Math.abs(roleKey.split('').reduce((a, c) => a + c.charCodeAt(0), 0)) % skinTones.length];
            if (agentData && agentData.skin_tone) {
                skinColor = parseInt(agentData.skin_tone.replace('#', '0x'), 16) || skinColor;
            }
            const skinMat = new THREE.MeshStandardMaterial({
                color: skinColor,
                roughness: 0.58,
                metalness: 0.04
            });

            // 2. High-End Tailored Suit Fabric
            const teamSuitColors = {
                'executive': 0x0f172a, // Midnight navy
                'marketing': 0x581c87, // Royal plum
                'sales': 0x0369a1,     // Deep sapphire
                'operations': 0x0f766e,// Deep spruce teal
                'finance': 0x065f46,   // Emerald forest
                'people': 0x9a3412     // Warm terracotta
            };
            let suitColor = teamSuitColors[pos.team] || 0x18181b;
            if (agentData && agentData.suit_color) {
                suitColor = parseInt(agentData.suit_color.replace('#', '0x'), 16) || suitColor;
            }
            const suitMat = new THREE.MeshStandardMaterial({
                color: suitColor,
                roughness: 0.72,
                metalness: 0.08
            });

            // Trouser / Pants Material
            const pantsMat = new THREE.MeshStandardMaterial({
                color: suitColor,
                roughness: 0.75,
                metalness: 0.06
            });

            // Inner Dress Shirt & Collar
            const shirtMat = new THREE.MeshStandardMaterial({
                color: 0xf8fafc, // Crisp white oxford
                roughness: 0.55,
                metalness: 0.02
            });

            // Team Tie Accent Color
            const teamTieColors = {
                'executive': 0xf59e0b, // Amber gold
                'marketing': 0xa855f7, // Amethyst
                'sales': 0x38bdf8,     // Azure
                'operations': 0x10b981,// Emerald
                'finance': 0x059669,   // Deep emerald
                'people': 0xf97316     // Tangerine
            };
            const tieMat = new THREE.MeshStandardMaterial({
                color: teamTieColors[pos.team] || 0x38bdf8,
                roughness: 0.35,
                metalness: 0.15
            });

            // Chrome Hardware (Belt buckle, tie clip, watch)
            const chromeMat = new THREE.MeshStandardMaterial({
                color: 0xe2e8f0,
                metalness: 0.92,
                roughness: 0.15
            });

            // Italian Oxford Leather Dress Shoes
            const shoeMat = new THREE.MeshStandardMaterial({
                color: 0x18181b,
                roughness: 0.22,
                metalness: 0.12
            });
            const soleMat = new THREE.MeshStandardMaterial({
                color: 0x271e18,
                roughness: 0.75
            });

            // ==========================================
            // A. PELVIS & TORSO WITH TAILORED SUIT & LAPELS
            // ==========================================
            // Pelvis Base
            const pelvis = new THREE.Mesh(new THREE.BoxGeometry(0.34, 0.12, 0.22), pantsMat);
            pelvis.position.y = 0.82;
            pelvis.castShadow = true;
            bodyPivot.add(pelvis);

            // Leather Belt & Silver Buckle at Waistline
            const belt = new THREE.Mesh(new THREE.BoxGeometry(0.36, 0.035, 0.23), new THREE.MeshStandardMaterial({ color: 0x18181b, roughness: 0.4 }));
            belt.position.y = 0.88;
            bodyPivot.add(belt);

            const buckle = new THREE.Mesh(new THREE.BoxGeometry(0.07, 0.045, 0.015), chromeMat);
            buckle.position.set(0, 0.88, 0.12);
            bodyPivot.add(buckle);

            // Tailored Suit Jacket (Tapered Chest and Waist)
            const torsoGroup = new THREE.Group();
            torsoGroup.position.set(0, 1.16, 0);

            const torsoJacket = new THREE.Mesh(new THREE.BoxGeometry(0.42, 0.54, 0.24), suitMat);
            torsoJacket.castShadow = true;
            torsoGroup.add(torsoJacket);

            // Inner Crisp Shirt V-Panel
            const shirtV = new THREE.Mesh(new THREE.BoxGeometry(0.18, 0.42, 0.02), shirtMat);
            shirtV.position.set(0, 0.07, 0.122);
            torsoGroup.add(shirtV);

            // 3D Notch Lapels (Left & Right)
            const lapelGeo = new THREE.BoxGeometry(0.08, 0.36, 0.025);
            const leftLapel = new THREE.Mesh(lapelGeo, suitMat);
            leftLapel.position.set(-0.11, 0.08, 0.13);
            leftLapel.rotation.z = -0.12;
            torsoGroup.add(leftLapel);

            const rightLapel = new THREE.Mesh(lapelGeo, suitMat);
            rightLapel.position.set(0.11, 0.08, 0.13);
            rightLapel.rotation.z = 0.12;
            torsoGroup.add(rightLapel);

            // Silk Necktie & Knot
            const tieKnot = new THREE.Mesh(new THREE.BoxGeometry(0.06, 0.05, 0.03), tieMat);
            tieKnot.position.set(0, 0.23, 0.138);
            torsoGroup.add(tieKnot);

            const tieBlade = new THREE.Mesh(new THREE.BoxGeometry(0.05, 0.34, 0.015), tieMat);
            tieBlade.position.set(0, 0.04, 0.135);
            torsoGroup.add(tieBlade);

            // Metallic Tie Clip
            const tieClip = new THREE.Mesh(new THREE.BoxGeometry(0.04, 0.015, 0.01), chromeMat);
            tieClip.position.set(0, 0.06, 0.145);
            torsoGroup.add(tieClip);

            bodyPivot.add(torsoGroup);

            // ==========================================
            // B. NECK, HEAD & ULTRA-DETAILED 3D HAIR
            // ==========================================
            // Neck
            const neck = new THREE.Mesh(new THREE.CylinderGeometry(0.065, 0.075, 0.12, 16), skinMat);
            neck.position.y = 1.48;
            neck.castShadow = true;
            bodyPivot.add(neck);

            // Head Group
            const headGroup = new THREE.Group();
            headGroup.position.set(0, 1.66, 0);

            // Sculpted Head Cranium
            const headGeo = new THREE.SphereGeometry(0.152, 16, 16);
            headGeo.scale(0.88, 1.05, 0.95);
            const head = new THREE.Mesh(headGeo, skinMat);
            head.castShadow = true;
            headGroup.add(head);

            // Face Plane Definition
            const chin = new THREE.Mesh(new THREE.BoxGeometry(0.12, 0.08, 0.09), skinMat);
            chin.position.set(0, -0.09, 0.08);
            headGroup.add(chin);

            // Hair Material
            let hairColor = 0x18181b; // Default deep espresso/black
            if (agentData && agentData.hair_color) {
                hairColor = parseInt(agentData.hair_color.replace('#', '0x'), 16) || hairColor;
            }
            const hairMat = new THREE.MeshStandardMaterial({
                color: hairColor,
                roughness: 0.45,
                metalness: 0.06
            });

            // 3D Sculpted Hair Mesh Hierarchy
            const hairGroup = new THREE.Group();
            const hairStyle = (agentData && agentData.hair_style) || 'classic';

            // Base Crown Volume
            const crown = new THREE.Mesh(new THREE.SphereGeometry(0.162, 16, 12), hairMat);
            crown.position.set(0, 0.04, -0.01);
            crown.scale.set(0.92, 0.85, 0.98);
            hairGroup.add(crown);

            // Side Temples
            const leftTemple = new THREE.Mesh(new THREE.BoxGeometry(0.04, 0.14, 0.18), hairMat);
            leftTemple.position.set(-0.13, 0.02, 0.01);
            hairGroup.add(leftTemple);

            const rightTemple = new THREE.Mesh(new THREE.BoxGeometry(0.04, 0.14, 0.18), hairMat);
            rightTemple.position.set(0.13, 0.02, 0.01);
            hairGroup.add(rightTemple);

            if (hairStyle === 'long_bob' || hairStyle === 'bob') {
                // Sleek Long Bob framing jawline
                const leftBob = new THREE.Mesh(new THREE.BoxGeometry(0.05, 0.28, 0.18), hairMat);
                leftBob.position.set(-0.14, -0.06, 0.02);
                hairGroup.add(leftBob);

                const rightBob = new THREE.Mesh(new THREE.BoxGeometry(0.05, 0.28, 0.18), hairMat);
                rightBob.position.set(0.14, -0.06, 0.02);
                hairGroup.add(rightBob);
            } else if (hairStyle === 'cyber_fade' || hairStyle === 'buzz') {
                // Sharp skin fade
                const topCrop = new THREE.Mesh(new THREE.BoxGeometry(0.24, 0.06, 0.26), hairMat);
                topCrop.position.set(0, 0.16, 0.02);
                hairGroup.add(topCrop);
            } else {
                // Executive Side Part with natural sweep volume
                const partSweep = new THREE.Mesh(new THREE.BoxGeometry(0.26, 0.08, 0.26), hairMat);
                partSweep.position.set(0.02, 0.15, 0.03);
                partSweep.rotation.z = -0.08;
                hairGroup.add(partSweep);
            }

            headGroup.add(hairGroup);

            // Accessories (Designer Glasses, Visor, or Headset)
            const accessoryType = (agentData && agentData.accessory) || 'none';
            if (accessoryType === 'glasses' || pos.isLead) {
                const glassesFrame = new THREE.Group();
                const frameMat = new THREE.MeshStandardMaterial({ color: 0x0f172a, metalness: 0.8, roughness: 0.2 });
                const lensMat = new THREE.MeshPhysicalMaterial({ color: 0x38bdf8, transparent: true, opacity: 0.4, roughness: 0.1 });

                [-0.055, 0.055].forEach(gx => {
                    const rim = new THREE.Mesh(new THREE.CylinderGeometry(0.032, 0.032, 0.01, 16), frameMat);
                    rim.rotation.x = Math.PI / 2;
                    rim.position.set(gx, 0, 0.155);
                    glassesFrame.add(rim);

                    const lens = new THREE.Mesh(new THREE.CylinderGeometry(0.028, 0.028, 0.008, 16), lensMat);
                    lens.rotation.x = Math.PI / 2;
                    lens.position.set(gx, 0, 0.155);
                    glassesFrame.add(lens);
                });

                const bridge = new THREE.Mesh(new THREE.BoxGeometry(0.04, 0.01, 0.01), frameMat);
                bridge.position.set(0, 0, 0.155);
                glassesFrame.add(bridge);

                glassesFrame.position.set(0, 0.02, 0);
                headGroup.add(glassesFrame);
            } else if (accessoryType === 'cyber_visor') {
                const visor = new THREE.Mesh(new THREE.BoxGeometry(0.26, 0.045, 0.04), new THREE.MeshStandardMaterial({
                    color: 0x06b6d4,
                    emissive: 0x06b6d4,
                    emissiveIntensity: 0.9,
                    roughness: 0.1
                }));
                visor.position.set(0, 0.02, 0.15);
                headGroup.add(visor);
            }

            bodyPivot.add(headGroup);

            // ==========================================
            // C. ARTICULATED UPPER LIMBS (SHOULDER, ELBOW, CUFF, WATCH, HAND)
            // ==========================================
            // Left Arm
            const leftArmGroup = new THREE.Group();
            leftArmGroup.position.set(-0.26, 1.38, 0);

            const leftShoulder = new THREE.Mesh(new THREE.SphereGeometry(0.065, 12, 12), suitMat);
            leftArmGroup.add(leftShoulder);

            const leftUpperArm = new THREE.Mesh(new THREE.CylinderGeometry(0.058, 0.052, 0.28, 12), suitMat);
            leftUpperArm.position.y = -0.14;
            leftUpperArm.castShadow = true;
            leftArmGroup.add(leftUpperArm);

            const leftElbowGroup = new THREE.Group();
            leftElbowGroup.position.set(0, -0.28, 0);

            const leftElbowJoint = new THREE.Mesh(new THREE.SphereGeometry(0.048, 12, 12), suitMat);
            leftElbowGroup.add(leftElbowJoint);

            const leftForearm = new THREE.Mesh(new THREE.CylinderGeometry(0.050, 0.045, 0.26, 12), suitMat);
            leftForearm.position.y = -0.13;
            leftForearm.castShadow = true;
            leftElbowGroup.add(leftForearm);

            // Crisp White Dress Shirt Cuff protruding 2.5cm
            const leftCuff = new THREE.Mesh(new THREE.CylinderGeometry(0.051, 0.051, 0.035, 12), shirtMat);
            leftCuff.position.y = -0.25;
            leftElbowGroup.add(leftCuff);

            // Luxury Smartwatch on Left Wrist
            const watchBand = new THREE.Mesh(new THREE.CylinderGeometry(0.053, 0.053, 0.02, 12), chromeMat);
            watchBand.position.y = -0.255;
            leftElbowGroup.add(watchBand);

            const watchFace = new THREE.Mesh(new THREE.BoxGeometry(0.035, 0.025, 0.01), new THREE.MeshStandardMaterial({ color: 0x090d16, emissive: 0x38bdf8, emissiveIntensity: 0.5 }));
            watchFace.position.set(0, -0.255, 0.055);
            leftElbowGroup.add(watchFace);

            // Sculpted Hand (Palm + Opposed Thumb)
            const leftHand = new THREE.Mesh(new THREE.BoxGeometry(0.07, 0.10, 0.035), skinMat);
            leftHand.position.set(0, -0.32, 0);
            leftElbowGroup.add(leftHand);

            leftArmGroup.add(leftElbowGroup);
            bodyPivot.add(leftArmGroup);

            // Right Arm
            const rightArmGroup = new THREE.Group();
            rightArmGroup.position.set(0.26, 1.38, 0);

            const rightShoulder = new THREE.Mesh(new THREE.SphereGeometry(0.065, 12, 12), suitMat);
            rightArmGroup.add(rightShoulder);

            const rightUpperArm = new THREE.Mesh(new THREE.CylinderGeometry(0.058, 0.052, 0.28, 12), suitMat);
            rightUpperArm.position.y = -0.14;
            rightUpperArm.castShadow = true;
            rightArmGroup.add(rightUpperArm);

            const rightElbowGroup = new THREE.Group();
            rightElbowGroup.position.set(0, -0.28, 0);

            const rightElbowJoint = new THREE.Mesh(new THREE.SphereGeometry(0.048, 12, 12), suitMat);
            rightElbowGroup.add(rightElbowJoint);

            const rightForearm = new THREE.Mesh(new THREE.CylinderGeometry(0.050, 0.045, 0.26, 12), suitMat);
            rightForearm.position.y = -0.13;
            rightForearm.castShadow = true;
            rightElbowGroup.add(rightForearm);

            // Crisp White Dress Shirt Cuff
            const rightCuff = new THREE.Mesh(new THREE.CylinderGeometry(0.051, 0.051, 0.035, 12), shirtMat);
            rightCuff.position.y = -0.25;
            rightElbowGroup.add(rightCuff);

            // Sculpted Right Hand
            const rightHand = new THREE.Mesh(new THREE.BoxGeometry(0.07, 0.10, 0.035), skinMat);
            rightHand.position.set(0, -0.32, 0);
            rightElbowGroup.add(rightHand);

            rightArmGroup.add(rightElbowGroup);
            bodyPivot.add(rightArmGroup);

            // ==========================================
            // D. ARTICULATED LOWER LIMBS (HIP, KNEE, TROUSERS, OXFORD SHOES)
            // ==========================================
            // Left Leg
            const leftLegGroup = new THREE.Group();
            leftLegGroup.position.set(-0.12, 0.80, 0);

            const leftThigh = new THREE.Mesh(new THREE.CylinderGeometry(0.072, 0.065, 0.38, 12), pantsMat);
            leftThigh.position.y = -0.19;
            leftThigh.castShadow = true;
            leftLegGroup.add(leftThigh);

            const leftKneeGroup = new THREE.Group();
            leftKneeGroup.position.set(0, -0.38, 0);

            const leftKneeJoint = new THREE.Mesh(new THREE.SphereGeometry(0.060, 12, 12), pantsMat);
            leftKneeGroup.add(leftKneeJoint);

            const leftCalf = new THREE.Mesh(new THREE.CylinderGeometry(0.062, 0.055, 0.38, 12), pantsMat);
            leftCalf.position.y = -0.19;
            leftCalf.castShadow = true;
            leftKneeGroup.add(leftCalf);

            // Polished Oxford Shoe with Stacked Heel & Welt
            const leftShoe = new THREE.Group();
            leftShoe.position.set(0, -0.38, 0.04);

            const lShoeUpper = new THREE.Mesh(new THREE.BoxGeometry(0.11, 0.065, 0.22), shoeMat);
            lShoeUpper.castShadow = true;
            leftShoe.add(lShoeUpper);

            const lShoeSole = new THREE.Mesh(new THREE.BoxGeometry(0.115, 0.018, 0.23), soleMat);
            lShoeSole.position.y = -0.035;
            leftShoe.add(lShoeSole);

            const lShoeHeel = new THREE.Mesh(new THREE.BoxGeometry(0.115, 0.022, 0.08), soleMat);
            lShoeHeel.position.set(0, -0.045, -0.065);
            leftShoe.add(lShoeHeel);

            leftKneeGroup.add(leftShoe);
            leftLegGroup.add(leftKneeGroup);
            bodyPivot.add(leftLegGroup);

            // Right Leg
            const rightLegGroup = new THREE.Group();
            rightLegGroup.position.set(0.12, 0.80, 0);

            const rightThigh = new THREE.Mesh(new THREE.CylinderGeometry(0.072, 0.065, 0.38, 12), pantsMat);
            rightThigh.position.y = -0.19;
            rightThigh.castShadow = true;
            rightLegGroup.add(rightThigh);

            const rightKneeGroup = new THREE.Group();
            rightKneeGroup.position.set(0, -0.38, 0);

            const rightKneeJoint = new THREE.Mesh(new THREE.SphereGeometry(0.060, 12, 12), pantsMat);
            rightKneeGroup.add(rightKneeJoint);

            const rightCalf = new THREE.Mesh(new THREE.CylinderGeometry(0.062, 0.055, 0.38, 12), pantsMat);
            rightCalf.position.y = -0.19;
            rightCalf.castShadow = true;
            rightKneeGroup.add(rightCalf);

            // Polished Oxford Shoe
            const rightShoe = new THREE.Group();
            rightShoe.position.set(0, -0.38, 0.04);

            const rShoeUpper = new THREE.Mesh(new THREE.BoxGeometry(0.11, 0.065, 0.22), shoeMat);
            rShoeUpper.castShadow = true;
            rightShoe.add(rShoeUpper);

            const rShoeSole = new THREE.Mesh(new THREE.BoxGeometry(0.115, 0.018, 0.23), soleMat);
            rShoeSole.position.y = -0.035;
            rightShoe.add(rShoeSole);

            const rShoeHeel = new THREE.Mesh(new THREE.BoxGeometry(0.115, 0.022, 0.08), soleMat);
            rShoeHeel.position.set(0, -0.045, -0.065);
            rightShoe.add(rShoeHeel);

            rightKneeGroup.add(rightShoe);
            rightLegGroup.add(rightKneeGroup);
            bodyPivot.add(rightLegGroup);

            // ==========================================
            // E. FACETED EMERALD PLUMBOB CRYSTAL JEWEL
            // ==========================================
            const plumbobMat = new THREE.MeshStandardMaterial({
                color: 0x10b981,
                emissive: 0x059669,
                emissiveIntensity: 0.85,
                roughness: 0.12,
                metalness: 0.1
            });
            const plumbobGeo = new THREE.OctahedronGeometry(0.15, 0);
            plumbobGeo.scale(0.8, 1.85, 0.8);
            const plumbob = new THREE.Mesh(plumbobGeo, plumbobMat);
            plumbob.position.set(0, 2.05, 0);
            bodyPivot.add(plumbob);
            plumbob.userData = { roleKey, agentData, type: 'agent', character: charGroup };
            this.interactiveObjects.push(plumbob);

            // Floating Name Badge Tag (High-Resolution Apple HIG Frosted Glass)
            const badgeSprite = this.createSimsBadgeTagSprite(pos.title, pos.roleBadge || (agentData.status || 'Aktif'), agentData);
            badgeSprite.position.set(0, 2.45, 0);
            bodyPivot.add(badgeSprite);
            badgeSprite.userData = { roleKey, agentData, type: 'agent', character: charGroup };
            this.interactiveObjects.push(badgeSprite);

            // Raycast Collider for Character Model (User can click agent directly to assign task)
            const charHitBox = new THREE.Mesh(
                new THREE.BoxGeometry(1.0, 2.2, 1.0),
                new THREE.MeshBasicMaterial({ visible: false })
            );
            charHitBox.position.set(0, 1.1, 0);
            charHitBox.userData = { roleKey, agentData, type: 'agent', character: charGroup };
            charGroup.add(charHitBox);
            this.interactiveObjects.push(charHitBox);

            const simsRecord = {
                roleKey,
                agentData,
                team: pos.team,
                group: charGroup,
                bodyPivot,
                torso: torsoGroup,
                head: headGroup,
                headGroup,
                plumbob,
                leftArm: leftArmGroup,
                rightArm: rightArmGroup,
                leftElbow: leftElbowGroup,
                rightElbow: rightElbowGroup,
                leftLeg: leftLegGroup,
                rightLeg: rightLegGroup,
                leftKnee: leftKneeGroup,
                rightKnee: rightKneeGroup,
                leftFoot: leftShoe,
                rightFoot: rightShoe,
                leftShoe,
                rightShoe,
                badgeSprite,
                thoughtBubble: null,
                thoughtBubbleTimeout: null,
                homePos: { x: worldTransform.x, y: 0, z: worldTransform.z, rot: worldTransform.rot },
                currentState: 'AT_DESK',
                currentChair: this.chairRegistry.get('desk_' + roleKey),
                deskActivity: 'TYPING',
                nextActivityChange: Date.now() + 3000 + Math.random() * 5000,
                coffeePhase: 0,
                coffeeTimer: 0,
                coffeeMug: (workstationGroup && workstationGroup.userData) ? workstationGroup.userData.coffeeMug : null,
                floorY: 0,
                walkSpeed: 3.8,
                nextActionTime: Date.now() + 8000 + Math.random() * 10000,
                pathWaypoints: [],
                currentPathIndex: 0,
                targetWaypoint: null,
                finalTargetPos: null,
                arrivalCallback: null,
                isTaskActive: false,
                currentTask: null,
                monitor: monitorMesh,
                workstation: workstationGroup
            };

            this.simsCharacters.set(roleKey, simsRecord);

            // Initial pose: Seated ergonomically at private desk (seatHeight 0.46m)
            this.setSeatedPose(simsRecord, { seatHeight: 0.46, isDesk: true });

            return charGroup;
        }

        createSimsBadgeTagSprite(name, badgeText, agentData) {
            const canvas = document.createElement('canvas');
            canvas.width = 512;
            canvas.height = 128;
            const ctx = canvas.getContext('2d');

            // High-DPI Apple HIG frosted acrylic glass pill
            const gradient = ctx.createLinearGradient(0, 0, 0, 128);
            gradient.addColorStop(0, 'rgba(15, 23, 42, 0.94)');
            gradient.addColorStop(1, 'rgba(30, 41, 59, 0.92)');
            ctx.fillStyle = gradient;
            this.roundRect(ctx, 12, 12, 488, 104, 24);
            ctx.fill();

            ctx.strokeStyle = 'rgba(255, 255, 255, 0.22)';
            ctx.lineWidth = 2.5;
            ctx.stroke();

            // Status indicator pill with glow
            const status = (agentData.status || 'idle').toUpperCase();
            const statusColor = status === 'WORKING' ? '#10b981' : (status === 'WAITING_APPROVAL' ? '#f59e0b' : '#38bdf8');
            ctx.shadowColor = statusColor;
            ctx.shadowBlur = 10;
            ctx.fillStyle = statusColor;
            ctx.beginPath();
            ctx.arc(44, 64, 11, 0, Math.PI * 2);
            ctx.fill();
            ctx.shadowBlur = 0;

            // Character Name Typography
            ctx.fillStyle = '#ffffff';
            ctx.font = 'bold 28px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
            ctx.fillText(name, 68, 56);

            // Subtitle / Status text
            ctx.fillStyle = '#94a3b8';
            ctx.font = '600 20px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
            ctx.fillText('● ' + badgeText, 68, 92);

            const texture = new THREE.CanvasTexture(canvas);
            const sprite = new THREE.Sprite(new THREE.SpriteMaterial({ map: texture, transparent: true }));
            sprite.scale.set(2.6, 0.65, 1);
            return sprite;
        }

        updateAgentBadgeStatus(roleKey, statusText) {
            const sims = this.simsCharacters.get(roleKey);
            if (!sims || !sims.badgeSprite) return;

            const title = (sims.agentData && sims.agentData.name) || roleKey.toUpperCase();
            const canvas = document.createElement('canvas');
            canvas.width = 512;
            canvas.height = 128;
            const ctx = canvas.getContext('2d');

            const gradient = ctx.createLinearGradient(0, 0, 0, 128);
            gradient.addColorStop(0, 'rgba(15, 23, 42, 0.94)');
            gradient.addColorStop(1, 'rgba(30, 41, 59, 0.92)');
            ctx.fillStyle = gradient;
            this.roundRect(ctx, 12, 12, 488, 104, 24);
            ctx.fill();

            ctx.strokeStyle = '#10b981';
            ctx.lineWidth = 2.5;
            ctx.stroke();

            ctx.shadowColor = '#10b981';
            ctx.shadowBlur = 12;
            ctx.fillStyle = '#10b981';
            ctx.beginPath();
            ctx.arc(44, 64, 11, 0, Math.PI * 2);
            ctx.fill();
            ctx.shadowBlur = 0;

            ctx.fillStyle = '#ffffff';
            ctx.font = 'bold 28px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
            ctx.fillText(title, 68, 56);

            ctx.fillStyle = '#34d399';
            ctx.font = '600 20px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
            ctx.fillText('● ' + statusText, 68, 92);

            if (sims.badgeSprite.material.map) {
                sims.badgeSprite.material.map.dispose();
            }
            sims.badgeSprite.material.map = new THREE.CanvasTexture(canvas);
            sims.badgeSprite.material.needsUpdate = true;
        }

        showThoughtBubble(sims, text, duration = 8000) {
            if (!sims || !sims.group) return;

            if (sims.thoughtBubble) {
                sims.group.remove(sims.thoughtBubble);
                if (sims.thoughtBubble.material.map) sims.thoughtBubble.material.map.dispose();
                sims.thoughtBubble.material.dispose();
                sims.thoughtBubble = null;
            }
            if (sims.thoughtBubbleTimeout) {
                clearTimeout(sims.thoughtBubbleTimeout);
                sims.thoughtBubbleTimeout = null;
            }

            const canvas = document.createElement('canvas');
            canvas.width = 512;
            canvas.height = 140;
            const ctx = canvas.getContext('2d');

            ctx.fillStyle = 'rgba(15, 23, 42, 0.95)';
            this.roundRect(ctx, 16, 12, 480, 96, 20);
            ctx.fill();

            ctx.strokeStyle = '#38bdf8';
            ctx.lineWidth = 3;
            ctx.stroke();

            ctx.beginPath();
            ctx.moveTo(240, 108);
            ctx.lineTo(256, 132);
            ctx.lineTo(272, 108);
            ctx.fillStyle = 'rgba(15, 23, 42, 0.95)';
            ctx.fill();
            ctx.stroke();

            ctx.fillStyle = '#38bdf8';
            ctx.font = 'bold 20px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
            ctx.fillText('💡 TUGAS AKTIF:', 36, 44);

            ctx.fillStyle = '#f8fafc';
            ctx.font = '500 20px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
            const displayStr = text.length > 36 ? text.substring(0, 34) + '...' : text;
            ctx.fillText(displayStr, 36, 76);

            const texture = new THREE.CanvasTexture(canvas);
            const sprite = new THREE.Sprite(new THREE.SpriteMaterial({ map: texture, transparent: true }));
            sprite.scale.set(3.4, 0.95, 1);
            sprite.position.set(0, 3.4, 0);

            sims.group.add(sprite);
            sims.thoughtBubble = sprite;

            sims.thoughtBubbleTimeout = setTimeout(() => {
                if (sims.thoughtBubble) {
                    sims.group.remove(sims.thoughtBubble);
                    if (sims.thoughtBubble.material.map) sims.thoughtBubble.material.map.dispose();
                    sims.thoughtBubble.material.dispose();
                    sims.thoughtBubble = null;
                }
            }, duration);
        }

        // ==========================================
        // ANATOMICALLY ACCURATE POSTURES
        // ==========================================
        setSeatedPose(sims, options = {}) {
            if (!sims) return;
            const isDesk = !!options.isDesk;

            // Lower body into seat cushion (pelvis drops from 0.82m down to 0.46m)
            if (sims.bodyPivot) {
                sims.bodyPivot.position.y = -0.36;
                sims.bodyPivot.rotation.set(0, 0, 0);
            }

            // Ensure character model is level with zero pitch/roll
            if (sims.group) {
                sims.group.rotation.x = 0;
                sims.group.rotation.z = 0;
            }

            // Hips horizontal: thighs extend forward (+Z in character local coordinate space)
            if (sims.leftLeg) sims.leftLeg.rotation.set(-Math.PI / 2, 0, 0);
            if (sims.rightLeg) sims.rightLeg.rotation.set(-Math.PI / 2, 0, 0);

            // Knees flexed 90 degrees downward: calves vertical (-Y in world coordinate space)
            if (sims.leftKnee) sims.leftKnee.rotation.set(Math.PI / 2, 0, 0);
            if (sims.rightKnee) sims.rightKnee.rotation.set(Math.PI / 2, 0, 0);

            // Feet flat on carpet
            if (sims.leftFoot) sims.leftFoot.rotation.set(0, 0, 0);
            if (sims.rightFoot) sims.rightFoot.rotation.set(0, 0, 0);

            if (isDesk) {
                // Ergonomic typing posture: upper arms forward, hands cleanly resting on keyboard/desk at 0.75m
                if (sims.leftArm) sims.leftArm.rotation.set(-Math.PI / 4.2, 0, -0.06);
                if (sims.rightArm) sims.rightArm.rotation.set(-Math.PI / 4.2, 0, 0.06);
                if (sims.leftElbow) sims.leftElbow.rotation.set(Math.PI / 3.4, 0, 0);
                if (sims.rightElbow) sims.rightElbow.rotation.set(Math.PI / 3.4, 0, 0);
                if (sims.head) sims.head.rotation.set(0.08, 0, 0); // Focus gaze comfortably on center of 27" monitor at 1.05m
            } else {
                // Relaxed lounge / meeting posture: hands casually on lap
                if (sims.leftArm) sims.leftArm.rotation.set(-Math.PI / 6, 0, -0.05);
                if (sims.rightArm) sims.rightArm.rotation.set(-Math.PI / 6, 0, 0.05);
                if (sims.leftElbow) sims.leftElbow.rotation.set(Math.PI / 5, 0, 0);
                if (sims.rightElbow) sims.rightElbow.rotation.set(Math.PI / 5, 0, 0);
                if (sims.head) sims.head.rotation.set(0, 0, 0);
            }
        }

        setStandingPose(sims) {
            if (!sims) return;

            if (sims.bodyPivot) {
                sims.bodyPivot.position.y = 0;
                sims.bodyPivot.rotation.set(0, 0, 0);
            }

            // Upright legs
            if (sims.leftLeg) sims.leftLeg.rotation.set(0, 0, 0);
            if (sims.rightLeg) sims.rightLeg.rotation.set(0, 0, 0);
            if (sims.leftKnee) sims.leftKnee.rotation.set(0, 0, 0);
            if (sims.rightKnee) sims.rightKnee.rotation.set(0, 0, 0);
            if (sims.leftFoot) sims.leftFoot.rotation.set(0, 0, 0);
            if (sims.rightFoot) sims.rightFoot.rotation.set(0, 0, 0);

            // Natural standing arm hang with slight relaxed elbow angle
            if (sims.leftArm) sims.leftArm.rotation.set(0, 0, 0);
            if (sims.rightArm) sims.rightArm.rotation.set(0, 0, 0);
            if (sims.leftElbow) sims.leftElbow.rotation.set(0.12, 0, 0);
            if (sims.rightElbow) sims.rightElbow.rotation.set(0.12, 0, 0);
            if (sims.head) sims.head.rotation.set(0, 0, 0);
        }

        getRoomForPos(pos) {
            if (!pos) return 'atrium';
            const x = pos.x;
            const z = pos.z;

            // Center Column: x in [-7.2, 7.2]
            if (x >= -7.2 && x <= 7.2) {
                if (z < -4.2) return 'executive';
                if (z > 5.5) return 'entrance';
                return 'atrium';
            }

            // West Wing: x < -7.2
            if (x < -7.2) {
                if (z < -5.0) return 'marketing';
                if (z > 2.5) return 'meeting';
                return 'sales';
            }

            // East Wing: x > 7.2
            if (x > 7.2) {
                if (z < -5.0) {
                    return x > 18.0 ? 'server' : 'operations';
                }
                if (z >= -5.0 && z < 1.5) {
                    return x > 18.0 ? 'document' : 'finance';
                }
                if (z >= 1.5 && z < 6.8) {
                    return 'pantry';
                }
                return 'restroom';
            }

            return 'atrium';
        }

        getDeskApproachNodeKey(roleKey) {
            const map = {
                ceo: 'exec_ceo_desk',
                cfo: 'exec_cfo_desk',
                business: 'exec_business_desk',
                cmo: 'mkt_desk_marketing',
                marketing: 'mkt_desk_marketing',
                content: 'mkt_desk_content',
                social_media: 'mkt_desk_social',
                sales_director: 'sales_desk_sales',
                sales: 'sales_desk_sales',
                customer: 'sales_desk_customer',
                coo: 'ops_desk_purchasing',
                inventory: 'ops_desk_inventory',
                purchasing: 'ops_desk_purchasing',
                marketplace: 'ops_desk_marketplace',
                finance: 'finance_desk_fin',
                reporting: 'finance_desk_rep',
                hr: 'hr_desk'
            };
            return map[roleKey] || 'atrium_center';
        }

        // ==========================================
        // ROOM-AWARE BFS PATHFINDING (ANTI-CLIPPING)
        // ==========================================
        findPath(startPos, endPos) {
            if (!this.navNodes || !this.navAdjacency) {
                return [new THREE.Vector3(endPos.x, 0, endPos.z)];
            }

            const startRoom = this.getRoomForPos(startPos);
            const endRoom = this.getRoomForPos(endPos);

            // Room-to-nodes mapping to prevent snapping across solid walls (13 Distinct Rooms)
            const roomNodes = {
                executive:  ['exec_boardroom_table', 'exec_ceo_approach', 'exec_ceo_desk', 'exec_hall_west', 'exec_cfo_desk', 'exec_hall_east', 'exec_business_desk', 'exec_hall_center', 'exec_door'],
                marketing:  ['mkt_collab_table', 'mkt_aisle', 'mkt_desk_marketing', 'mkt_desk_content', 'mkt_desk_social', 'mkt_door'],
                sales:      ['sales_hall', 'sales_workbench', 'sales_desk_sales', 'sales_desk_customer', 'growth_spine'],
                meeting:    ['meeting_table', 'meeting_tv', 'meeting_lounge_bench', 'meeting_door'],
                operations: ['ops_collab_table', 'ops_aisle', 'ops_desk_inventory', 'ops_desk_purchasing', 'ops_desk_marketplace', 'ops_room_door', 'hr_desk'],
                server:     ['server_room_bay', 'it_support_desk', 'server_door', 'hr_desk'],
                finance:    ['finance_hall', 'finance_desk_fin', 'finance_desk_rep', 'ops_spine'],
                document:   ['document_archive', 'doc_door'],
                people:     ['hr_desk', 'server_door'],
                pantry:     ['pantry_atrium_door', 'pantry_lounge', 'pantry_dining_area', 'pantry_kitchenette', 'pantry_door'],
                restroom:   ['restroom_area', 'restroom_door'],
                entrance:   ['reception_front', 'entrance_mat', 'entrance_doors'],
                atrium:     ['atrium_center', 'atrium_rotunda_north', 'atrium_rotunda_south', 'atrium_rotunda_west', 'atrium_rotunda_east', 'exec_door', 'growth_door', 'ops_door', 'pantry_atrium_door', 'reception_front']
            };

            const candidateStartKeys = (startRoom && roomNodes[startRoom]) ? roomNodes[startRoom] : Object.keys(this.navNodes);
            const candidateEndKeys = (endRoom && roomNodes[endRoom]) ? roomNodes[endRoom] : Object.keys(this.navNodes);

            let startNodeKey = candidateStartKeys[0] || 'atrium_center';
            let minStartDist = Infinity;
            for (const key of candidateStartKeys) {
                const node = this.navNodes[key];
                if (!node) continue;
                const dx = node.x - startPos.x;
                const dz = node.z - startPos.z;
                const d = dx * dx + dz * dz;
                if (d < minStartDist) {
                    minStartDist = d;
                    startNodeKey = key;
                }
            }

            let endNodeKey = candidateEndKeys[0] || 'atrium_center';
            let minEndDist = Infinity;
            for (const key of candidateEndKeys) {
                const node = this.navNodes[key];
                if (!node) continue;
                const dx = node.x - endPos.x;
                const dz = node.z - endPos.z;
                const d = dx * dx + dz * dz;
                if (d < minEndDist) {
                    minEndDist = d;
                    endNodeKey = key;
                }
            }

            // If within same room and same node, route directly to target
            if (startNodeKey === endNodeKey && startRoom === endRoom) {
                return [new THREE.Vector3(endPos.x, 0, endPos.z)];
            }

            const queue = [[startNodeKey]];
            const visited = new Set([startNodeKey]);
            let foundPathKeys = null;

            while (queue.length > 0) {
                const path = queue.shift();
                const current = path[path.length - 1];

                if (current === endNodeKey) {
                    foundPathKeys = path;
                    break;
                }

                const neighbors = this.navAdjacency[current] || [];
                for (const nbr of neighbors) {
                    if (!visited.has(nbr)) {
                        visited.add(nbr);
                        queue.push([...path, nbr]);
                    }
                }
            }

            const waypoints = [];
            if (foundPathKeys) {
                for (const key of foundPathKeys) {
                    if (this.navNodes[key]) {
                        waypoints.push(this.navNodes[key].clone());
                    }
                }
            } else {
                waypoints.push(this.navNodes['atrium_center'].clone());
            }

            waypoints.push(new THREE.Vector3(endPos.x, 0, endPos.z));
            return waypoints;
        }

        commandAgentFollowPath(sims, targetPos, arrivalCallback) {
            if (!sims) return;
            const currentPos = sims.group.position;
            const dx = targetPos.x - currentPos.x;
            const dz = targetPos.z - currentPos.z;
            const dist = Math.sqrt(dx * dx + dz * dz);

            if (dist < 0.45) {
                if (typeof arrivalCallback === 'function') {
                    arrivalCallback();
                }
                return;
            }

            const waypoints = this.findPath(currentPos, targetPos);
            sims.pathWaypoints = waypoints;
            sims.currentPathIndex = 0;
            sims.targetWaypoint = waypoints[0];
            sims.finalTargetPos = targetPos;
            sims.arrivalCallback = arrivalCallback;

            // Transition to walking gait
            this.setStandingPose(sims);
            sims.currentState = 'WALKING';
        }

        commandAgentToChair(sims, chair, arrivalCallback) {
            if (!sims || !chair) return;

            sims.currentChair = chair;
            const targetPos = new THREE.Vector3(chair.pos.x, 0, chair.pos.z);

            this.commandAgentFollowPath(sims, targetPos, () => {
                sims.currentChair = chair;
                sims.currentState = 'SEATED';
                sims.group.position.set(chair.pos.x, 0, chair.pos.z);
                sims.group.rotation.set(0, chair.rot, 0);

                const isDesk = chair.facility === 'desk';
                this.setSeatedPose(sims, {
                    seatHeight: chair.seatHeight || 0.46,
                    isDesk: isDesk
                });

                if (typeof arrivalCallback === 'function') {
                    arrivalCallback();
                }
            });
        }

        // Direct Task Execution for Single Agent
        assignTaskToAgent(roleKey, taskText) {
            const sims = this.simsCharacters.get(roleKey);
            if (!sims) return;

            sims.isTaskActive = true;
            sims.currentTask = taskText || 'Mengerjakan tugas langsung';

            this.showThoughtBubble(sims, sims.currentTask, 9000);
            this.updateAgentBadgeStatus(roleKey, 'Mengerjakan Tugas');

            if (sims.monitor && sims.monitor.material) {
                sims.monitor.material.emissive.setHex(0x10b981);
                sims.monitor.material.emissiveIntensity = 1.1;
            }

            if (sims.plumbob && sims.plumbob.material) {
                sims.plumbob.material.emissive.setHex(0x10b981);
                sims.plumbob.material.emissiveIntensity = 1.2;
            }

            // Release any lounge/pantry chair and return to private desk
            this.releaseSeat(roleKey);
            const deskChair = this.reserveSeat('desk', roleKey);

            if (deskChair) {
                this.commandAgentToChair(sims, deskChair, () => {
                    sims.currentChair = deskChair;
                    sims.currentState = 'AT_DESK';
                    sims.group.position.set(deskChair.pos.x, 0, deskChair.pos.z);
                    sims.group.rotation.set(0, deskChair.rot, 0);
                    this.setSeatedPose(sims, { isDesk: true, seatHeight: deskChair.seatHeight || 0.46 });
                    this.triggerKeystrokeAudio();
                });
            } else {
                const deskTarget = new THREE.Vector3(sims.homePos.x, 0, sims.homePos.z);
                this.commandAgentFollowPath(sims, deskTarget, () => {
                    sims.currentChair = this.chairRegistry.get('desk_' + roleKey) || null;
                    sims.currentState = 'AT_DESK';
                    sims.group.position.set(sims.homePos.x, 0, sims.homePos.z);
                    sims.group.rotation.set(0, sims.homePos.rot, 0);
                    this.setSeatedPose(sims, { isDesk: true, seatHeight: 0.46 });
                    this.triggerKeystrokeAudio();
                });
            }
        }

        // Direct Task Execution for Entire Team with Single-Occupancy Seating
        assignTaskToTeam(teamKey, taskText) {
            let facility = 'meeting';
            if (teamKey === 'executive') {
                facility = 'boardroom';
            } else if (teamKey === 'operations') {
                facility = 'control';
            } else if (teamKey === 'sales' || teamKey === 'marketing') {
                facility = 'meeting';
            }

            this.simsCharacters.forEach((sims) => {
                const matchTeam = sims.team === teamKey ||
                    (teamKey === 'general') ||
                    (teamKey === 'growth' && (sims.team === 'marketing' || sims.team === 'sales'));

                if (matchTeam) {
                    sims.isTaskActive = true;
                    sims.currentTask = taskText || `Briefing Tim ${teamKey.toUpperCase()}`;

                    this.showThoughtBubble(sims, `Tim ${teamKey}: ${sims.currentTask}`, 9000);
                    this.updateAgentBadgeStatus(sims.roleKey, 'Rapat Tim');

                    // Release prior seat and reserve dedicated individual chair in meeting room
                    this.releaseSeat(sims.roleKey);
                    const chair = this.reserveSeat(facility, sims.roleKey) || this.reserveSeat('focus', sims.roleKey) || this.reserveSeat('rotunda', sims.roleKey);

                    if (chair) {
                        this.commandAgentToChair(sims, chair, () => {
                            sims.currentChair = chair;
                            sims.currentState = 'AT_MEETING';
                            sims.group.position.set(chair.pos.x, 0, chair.pos.z);
                            sims.group.rotation.set(0, chair.rot, 0);
                            this.setSeatedPose(sims, { isDesk: false, seatHeight: chair.seatHeight || 0.46 });
                        });
                    } else {
                        // Fallback to approach node
                        const meetingDest = this.navNodes.meeting_table || this.navNodes.atrium_center;
                        this.commandAgentFollowPath(sims, meetingDest, () => {
                            sims.currentChair = null;
                            sims.currentState = 'AT_MEETING';
                            sims.group.position.set(meetingDest.x, 0, meetingDest.z);
                            this.setStandingPose(sims);
                        });
                    }
                }
            });
        }

        roundRect(ctx, x, y, width, height, radius) {
            ctx.beginPath();
            ctx.moveTo(x + radius, y);
            ctx.lineTo(x + width - radius, y);
            ctx.quadraticCurveTo(x + width, y, x + width, y + radius);
            ctx.lineTo(x + width, y + height - radius);
            ctx.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
            ctx.lineTo(x + radius, y + height);
            ctx.quadraticCurveTo(x, y + height, x, y + height - radius);
            ctx.lineTo(x, y + radius);
            ctx.quadraticCurveTo(x, y, x + radius, y);
            ctx.closePath();
        }

        hexToRgba(hex, alpha = 1) {
            const r = (hex >> 16) & 255;
            const g = (hex >> 8) & 255;
            const b = hex & 255;
            return `rgba(${r}, ${g}, ${b}, ${alpha})`;
        }

        // ==========================================
        // 4. THE SIMS AUTONOMOUS BEHAVIOR (GUARANTEED NO OVERLAP)
        // ==========================================
        initSimsAutonomy() {
            setInterval(() => {
                const now = Date.now();
                this.simsCharacters.forEach((sims) => {
                    if (now > sims.nextActionTime) {
                        this.pickNextSimsActivity(sims);
                    }
                });
            }, 2500);
        }

        pickNextSimsActivity(sims) {
            sims.nextActionTime = Date.now() + 14000 + Math.random() * 16000;

            // If user is controlling this agent in POV mode, skip autonomous AI logic
            if (this.isPOVMode && this.povRoleKey === sims.roleKey) return;

            // If agent is performing an assigned task, keep working at desk
            if (sims.isTaskActive) {
                if (sims.currentState === 'AT_DESK') {
                    this.triggerKeystrokeAudio();
                }
                return;
            }

            if (sims.currentState === 'AT_DESK') {
                const rand = Math.random();
                if (rand < 0.35) {
                    this.commandAgentGoToRotunda(sims.roleKey);
                } else if (rand < 0.6) {
                    this.commandAgentGoToPantry(sims.roleKey);
                } else {
                    this.triggerKeystrokeAudio();
                }
            } else {
                this.commandAgentReturnToDesk(sims.roleKey);
            }
        }

        commandAgentGoToRotunda(roleKey) {
            const sims = this.simsCharacters.get(roleKey);
            if (!sims) return;

            // Release current seat and reserve unique rotunda chair
            this.releaseSeat(roleKey);
            const chair = this.reserveSeat('rotunda', roleKey);
            if (!chair) {
                // All rotunda chairs occupied, stay at desk
                return;
            }

            this.commandAgentToChair(sims, chair, () => {
                sims.currentChair = chair;
                sims.currentState = 'ON_SOFA';
                sims.group.position.set(chair.pos.x, 0, chair.pos.z);
                sims.group.rotation.set(0, chair.rot, 0);
                this.setSeatedPose(sims, { isDesk: false, seatHeight: chair.seatHeight || 0.46 });
            });
        }

        commandAgentGoToPantry(roleKey) {
            const sims = this.simsCharacters.get(roleKey);
            if (!sims) return;

            // Release current seat and reserve unique pantry chair
            this.releaseSeat(roleKey);
            const chair = this.reserveSeat('pantry', roleKey);
            if (!chair) {
                // All pantry chairs occupied, stay at desk
                return;
            }

            this.commandAgentToChair(sims, chair, () => {
                sims.currentChair = chair;
                sims.currentState = 'AT_PANTRY';
                sims.group.position.set(chair.pos.x, 0, chair.pos.z);
                sims.group.rotation.set(0, chair.rot, 0);
                this.setSeatedPose(sims, { isDesk: false, seatHeight: chair.seatHeight || 0.46 });
            });
        }

        commandAgentReturnToDesk(roleKey) {
            const sims = this.simsCharacters.get(roleKey);
            if (!sims) return;

            this.releaseSeat(roleKey);
            const deskChair = this.reserveSeat('desk', roleKey);

            if (deskChair) {
                this.commandAgentToChair(sims, deskChair, () => {
                    sims.currentChair = deskChair;
                    sims.currentState = 'AT_DESK';
                    sims.group.position.set(deskChair.pos.x, 0, deskChair.pos.z);
                    sims.group.rotation.set(0, deskChair.rot, 0);
                    this.setSeatedPose(sims, { isDesk: true, seatHeight: deskChair.seatHeight || 0.46 });
                });
            } else {
                const deskPos = new THREE.Vector3(sims.homePos.x, 0, sims.homePos.z);
                this.commandAgentFollowPath(sims, deskPos, () => {
                    sims.currentChair = this.chairRegistry.get('desk_' + roleKey) || null;
                    sims.currentState = 'AT_DESK';
                    sims.group.position.set(sims.homePos.x, 0, sims.homePos.z);
                    sims.group.rotation.set(0, sims.homePos.rot, 0);
                    this.setSeatedPose(sims, { isDesk: true, seatHeight: 0.46 });
                });
            }
        }

        // ==========================================
        // 5. CAMERA PRESETS & ZOOM CONTROLS
        // ==========================================
        setCameraPreset(presetName) {
            const presets = {
                overview:       { pos: new THREE.Vector3(0, 32, 28), target: new THREE.Vector3(0, 0, -1) },
                executive:      { pos: new THREE.Vector3(0, 12, -2), target: new THREE.Vector3(0, 0, -10) },
                direksi:        { pos: new THREE.Vector3(0, 12, -2), target: new THREE.Vector3(0, 0, -10) },
                boardroom:      { pos: new THREE.Vector3(0, 9.5, -2), target: new THREE.Vector3(0, 0, -7.5) },
                growth:         { pos: new THREE.Vector3(-16, 16, 10), target: new THREE.Vector3(-16, 0, -4) },
                marketing:      { pos: new THREE.Vector3(-16, 12, -3), target: new THREE.Vector3(-16, 0, -10.5) },
                sales:          { pos: new THREE.Vector3(-16, 11, 5.5), target: new THREE.Vector3(-16, 0, -1.5) },
                meeting:        { pos: new THREE.Vector3(-16, 12, 13.5), target: new THREE.Vector3(-16, 0, 6.5) },
                operations:     { pos: new THREE.Vector3(13.5, 12, -3), target: new THREE.Vector3(13.5, 0, -10.5) },
                operasional:    { pos: new THREE.Vector3(13.5, 12, -3), target: new THREE.Vector3(13.5, 0, -10.5) },
                server:         { pos: new THREE.Vector3(21.3, 10.5, -3.5), target: new THREE.Vector3(21.3, 0, -10.5) },
                finance:        { pos: new THREE.Vector3(12.6, 11, 4.5), target: new THREE.Vector3(12.6, 0, -1.8) },
                document:       { pos: new THREE.Vector3(21.3, 10.5, 4.5), target: new THREE.Vector3(21.3, 0, -1.8) },
                hr:             { pos: new THREE.Vector3(16.0, 9.5, -2.0), target: new THREE.Vector3(16.0, 0, -7.5) },
                people:         { pos: new THREE.Vector3(16.0, 9.5, -2.0), target: new THREE.Vector3(16.0, 0, -7.5) },
                pantry:         { pos: new THREE.Vector3(20.1, 10.5, 10.5), target: new THREE.Vector3(20.1, 0, 4.2) },
                restroom:       { pos: new THREE.Vector3(16.0, 10, 15), target: new THREE.Vector3(16.0, 0, 8.8) },
                reception:      { pos: new THREE.Vector3(0, 11, 14.5), target: new THREE.Vector3(0, 0, 7.5) },
                entrance:       { pos: new THREE.Vector3(0, 11, 14.5), target: new THREE.Vector3(0, 0, 7.5) },
                atrium:         { pos: new THREE.Vector3(0, 14, 8), target: new THREE.Vector3(0, 0, 0.5) },
                rotunda:        { pos: new THREE.Vector3(0, 14, 8), target: new THREE.Vector3(0, 0, 0.5) },
                lobi:           { pos: new THREE.Vector3(0, 14, 8), target: new THREE.Vector3(0, 0, 0.5) }
            };

            const p = presets[presetName] || presets.overview;
            this.smoothGlideCamera(p.pos, p.target);
        }

        smoothGlideCamera(targetPos, targetLookAt) {
            this.targetCameraPos = targetPos.clone();
            this.targetLookAt = targetLookAt.clone();
            this.isLerpingCamera = true;
        }

        focusOnAgent(roleKey) {
            const sims = this.simsCharacters.get(roleKey);
            if (sims && sims.group) {
                const p = sims.group.position;
                const camPos = new THREE.Vector3(p.x, p.y + 4.2, p.z + 5.2);
                const lookAt = new THREE.Vector3(p.x, p.y + 1.2, p.z);
                this.smoothGlideCamera(camPos, lookAt);
                this.selectedAgentRole = roleKey;
                if (typeof this.onSelectAgent === 'function') {
                    this.onSelectAgent(roleKey, sims.agentData || {});
                }
            } else if (this.deskLocations && this.deskLocations[roleKey]) {
                const d = this.deskLocations[roleKey];
                const camPos = new THREE.Vector3(d.x, d.y + 4.2, d.z + 5.2);
                const lookAt = new THREE.Vector3(d.x, d.y + 1.2, d.z);
                this.smoothGlideCamera(camPos, lookAt);
                this.selectedAgentRole = roleKey;
                if (typeof this.onSelectAgent === 'function') {
                    this.onSelectAgent(roleKey, {});
                }
            }
        }

        zoomIn() {
            if (!this.camera || !this.controls) return;
            const dir = new THREE.Vector3().subVectors(this.camera.position, this.controls.target);
            dir.multiplyScalar(0.85);
            this.camera.position.addVectors(this.controls.target, dir);
            this.controls.update();
        }

        zoomOut() {
            if (!this.camera || !this.controls) return;
            const dir = new THREE.Vector3().subVectors(this.camera.position, this.controls.target);
            dir.multiplyScalar(1.15);
            this.camera.position.addVectors(this.controls.target, dir);
            this.controls.update();
        }

        triggerKeystrokeAudio() {
            if (!this.audioEnabled) return;
            const now = Date.now();
            if (now - this.lastSoundTime < 300) return;
            this.lastSoundTime = now;

            try {
                if (!this.audioCtx) {
                    this.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                }
                const osc = this.audioCtx.createOscillator();
                const gain = this.audioCtx.createGain();
                osc.type = 'triangle';
                osc.frequency.setValueAtTime(600 + Math.random() * 400, this.audioCtx.currentTime);
                gain.gain.setValueAtTime(0.02, this.audioCtx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.0001, this.audioCtx.currentTime + 0.05);
                osc.connect(gain);
                gain.connect(this.audioCtx.destination);
                osc.start();
                osc.stop(this.audioCtx.currentTime + 0.05);
            } catch (e) {}
        }

        // ==========================================
        // 5.5 POV & AGENT FIRST/THIRD PERSON CONTROLS
        // ==========================================
        enterAgentPOV(roleKey, povType = 'third_person') {
            const sims = this.simsCharacters.get(roleKey);
            if (!sims) {
                console.warn('[Cooca3DOffice] Cannot enter POV: agent not found', roleKey);
                return;
            }

            this.isPOVMode = true;
            this.povRoleKey = roleKey;
            this.povType = povType; // 'third_person' or 'first_person'
            this.povPitch = 0;
            this.activeMovementKeys.clear();

            if (this.controls) {
                this.controls.enabled = false;
            }

            this.updatePOVVisibility();

            const detail = {
                isPOV: true,
                roleKey: roleKey,
                povType: this.povType,
                agentData: sims.agentData || null
            };
            if (typeof this.onPOVChange === 'function') {
                this.onPOVChange(detail);
            }
            if (typeof window !== 'undefined') {
                window.dispatchEvent(new CustomEvent('cooca-pov-change', { detail }));
            }
        }

        exitAgentPOV() {
            if (!this.isPOVMode) return;

            const prevRole = this.povRoleKey;
            const sims = this.simsCharacters.get(prevRole);

            this.isPOVMode = false;
            this.povRoleKey = null;
            this.activeMovementKeys.clear();

            if (sims) {
                if (sims.head) sims.head.visible = true;
                if (sims.plumbob) sims.plumbob.visible = true;
                if (sims.badgeSprite) sims.badgeSprite.visible = true;
            }

            if (this.controls) {
                this.controls.enabled = true;
                if (sims) {
                    this.controls.target.copy(sims.group.position);
                    this.camera.position.set(
                        sims.group.position.x,
                        sims.group.position.y + 4.5,
                        sims.group.position.z + 5.5
                    );
                    this.controls.update();
                }
            }

            const detail = {
                isPOV: false,
                roleKey: null,
                povType: this.povType
            };
            if (typeof this.onPOVChange === 'function') {
                this.onPOVChange(detail);
            }
            if (typeof window !== 'undefined') {
                window.dispatchEvent(new CustomEvent('cooca-pov-change', { detail }));
            }
        }

        togglePOVType() {
            if (!this.isPOVMode) return;
            this.povType = this.povType === 'first_person' ? 'third_person' : 'first_person';
            this.updatePOVVisibility();

            const sims = this.simsCharacters.get(this.povRoleKey);
            const detail = {
                isPOV: true,
                roleKey: this.povRoleKey,
                povType: this.povType,
                agentData: sims ? sims.agentData : null
            };
            if (typeof this.onPOVChange === 'function') {
                this.onPOVChange(detail);
            }
            if (typeof window !== 'undefined') {
                window.dispatchEvent(new CustomEvent('cooca-pov-change', { detail }));
            }
        }

        updatePOVVisibility() {
            if (!this.povRoleKey) return;
            const sims = this.simsCharacters.get(this.povRoleKey);
            if (!sims) return;

            const isFirstPerson = this.isPOVMode && this.povType === 'first_person';
            if (sims.head) sims.head.visible = !isFirstPerson;
            if (sims.plumbob) sims.plumbob.visible = !isFirstPerson;
            if (sims.badgeSprite) sims.badgeSprite.visible = !isFirstPerson;
        }

        sitOrStandCurrentAgent() {
            if (!this.isPOVMode || !this.povRoleKey) return;
            const sims = this.simsCharacters.get(this.povRoleKey);
            if (!sims) return;

            const isSeated = sims.currentState === 'AT_DESK' || sims.currentState === 'SEATED' || sims.currentState === 'ON_SOFA' || sims.currentState === 'AT_PANTRY' || sims.currentState === 'AT_MEETING';

            if (isSeated) {
                this.releaseSeat(this.povRoleKey);
                sims.currentChair = null;
                sims.currentState = 'IDLE';
                this.setStandingPose(sims);
                const rot = sims.group.rotation.y;
                sims.group.position.x += Math.sin(rot) * 0.45;
                sims.group.position.z += Math.cos(rot) * 0.45;
            } else {
                const charPos = sims.group.position;
                let closestChair = null;
                let closestDist = 2.5;

                const ownDeskChair = this.chairRegistry.get('desk_' + this.povRoleKey);
                if (ownDeskChair && charPos.distanceTo(ownDeskChair.pos) < 2.5) {
                    closestChair = ownDeskChair;
                } else {
                    for (const chair of this.chairRegistry.values()) {
                        if (chair.occupiedBy && chair.occupiedBy !== this.povRoleKey) continue;
                        const d = charPos.distanceTo(chair.pos);
                        if (d < closestDist) {
                            closestDist = d;
                            closestChair = chair;
                        }
                    }
                }

                if (closestChair) {
                    closestChair.occupiedBy = this.povRoleKey;
                    sims.currentChair = closestChair;
                    sims.currentState = closestChair.id.startsWith('desk_') ? 'AT_DESK' : 'SEATED';
                    sims.group.position.set(closestChair.pos.x, 0, closestChair.pos.z);
                    sims.group.rotation.set(0, closestChair.rot, 0);
                    this.setSeatedPose(sims, {
                        seatHeight: closestChair.seatHeight || 0.46,
                        isDesk: closestChair.id.startsWith('desk_')
                    });
                }
            }
        }

        // ==========================================
        // 4B. GLASS DOORS & COLLISION ENGINE
        // ==========================================
        updateDoors(delta) {
            if (!this.doors || this.doors.size === 0) return;

            const now = Date.now();
            const OPEN_DIST = 2.1;   // Proximity trigger distance to begin opening
            const CLOSE_DIST = 2.5;  // Distance required to close door after agents leave

            // Collect active walking / approaching agents
            const activeAgents = [];

            // 1. Controlled POV Agent (Player)
            if (this.isPOVMode && this.povRoleKey) {
                const povSims = this.simsCharacters.get(this.povRoleKey);
                if (povSims && povSims.group) {
                    activeAgents.push(povSims.group.position);
                }
            }

            // 2. Autonomous NPC Agents (Only if walking or standing near doorway)
            this.simsCharacters.forEach((sims) => {
                if (this.isPOVMode && this.povRoleKey === sims.roleKey) return;
                if (sims.currentState === 'WALKING' || sims.currentState === 'IDLE') {
                    activeAgents.push(sims.group.position);
                }
            });

            this.doors.forEach((door) => {
                let agentNearby = false;

                // Check distance to all active agents
                for (let i = 0; i < activeAgents.length; i++) {
                    const aPos = activeAgents[i];
                    const dx = aPos.x - door.center.x;
                    const dz = aPos.z - door.center.z;
                    const distSq = dx * dx + dz * dz;

                    if (distSq < OPEN_DIST * OPEN_DIST) {
                        agentNearby = true;
                        break;
                    }
                }

                // If an agent is near and door isn't locked by manual toggle
                if (agentNearby) {
                    if (!door.manualLockUntil || now > door.manualLockUntil) {
                        door.targetAngle = door.maxAngle;
                    }
                } else {
                    // Check if all agents are beyond CLOSE_DIST before closing
                    let anyAgentClose = false;
                    for (let i = 0; i < activeAgents.length; i++) {
                        const aPos = activeAgents[i];
                        const dx = aPos.x - door.center.x;
                        const dz = aPos.z - door.center.z;
                        if (dx * dx + dz * dz < CLOSE_DIST * CLOSE_DIST) {
                            anyAgentClose = true;
                            break;
                        }
                    }
                    if (!anyAgentClose) {
                        door.targetAngle = 0;
                    }
                }

                const prevAngle = door.currentAngle;
                // Silky-smooth damped swing animation
                door.currentAngle = THREE.MathUtils.damp(door.currentAngle, door.targetAngle, 6.0, delta);

                // Update physical 3D hinge pivots
                if (door.leftPivot) {
                    door.leftPivot.rotation.y = door.currentAngle;
                }
                if (door.rightPivot) {
                    door.rightPivot.rotation.y = -door.currentAngle;
                }

                // State bookkeeping
                if (door.currentAngle > door.maxAngle * 0.88) {
                    door.state = 'open';
                } else if (door.targetAngle > 0.1) {
                    door.state = 'opening';
                } else if (door.currentAngle < 0.05) {
                    door.state = 'closed';
                } else {
                    door.state = 'closing';
                }

                // Sound effects for door opening/closing
                if (this.audioEnabled) {
                    if (prevAngle < 0.05 && door.currentAngle >= 0.05) {
                        this.playDoorSound('open');
                    } else if (prevAngle > 0.05 && door.currentAngle < 0.05) {
                        this.playDoorSound('close');
                    }
                }
            });
        }

        checkCollision(x, z, radius = 0.38) {
            // 1. Campus exterior building perimeter boundaries
            if (x - radius < -24.2 || x + radius > 24.2 || z - radius < -15.2 || z + radius > 9.8) {
                return true;
            }

            // 2. Solid frosted glass partition walls
            if (this.wallColliders && this.wallColliders.length > 0) {
                for (let i = 0; i < this.wallColliders.length; i++) {
                    const box = this.wallColliders[i];
                    if (
                        x + radius > box.minX &&
                        x - radius < box.maxX &&
                        z + radius > box.minZ &&
                        z - radius < box.maxZ
                    ) {
                        return true;
                    }
                }
            }

            // 3. Doors that are currently closed or swinging shut (blocked if angle < 0.38 rad ~ 22 deg)
            if (this.doors && this.doors.size > 0) {
                for (const door of this.doors.values()) {
                    if (door.currentAngle < 0.38 && door.collider) {
                        const box = door.collider;
                        if (
                            x + radius > box.minX &&
                            x - radius < box.maxX &&
                            z + radius > box.minZ &&
                            z - radius < box.maxZ
                        ) {
                            return true; // Door is closed, passage blocked!
                        }
                    }
                }
            }

            return false;
        }

        toggleNearestDoor() {
            if (!this.isPOVMode || !this.povRoleKey) return;
            const sims = this.simsCharacters.get(this.povRoleKey);
            if (!sims) return;
            const pos = sims.group.position;

            let nearestDoor = null;
            let minDist = 2.6;

            for (const door of this.doors.values()) {
                const d = pos.distanceTo(door.center);
                if (d < minDist) {
                    minDist = d;
                    nearestDoor = door;
                }
            }

            if (nearestDoor) {
                if (nearestDoor.targetAngle > 0.1) {
                    nearestDoor.targetAngle = 0;
                    nearestDoor.manualLockUntil = Date.now() + 3500; // Keep closed for 3.5s
                } else {
                    nearestDoor.targetAngle = nearestDoor.maxAngle;
                    nearestDoor.manualLockUntil = 0;
                }
                if (this.audioEnabled) {
                    this.playDoorSound(nearestDoor.targetAngle > 0 ? 'open' : 'close');
                }
            }
        }

        toggleDoor(doorId) {
            const door = this.doors.get(doorId);
            if (!door) return;
            if (door.targetAngle > 0.1) {
                door.targetAngle = 0;
                door.manualLockUntil = Date.now() + 4000;
            } else {
                door.targetAngle = door.maxAngle;
                door.manualLockUntil = 0;
            }
            if (this.audioEnabled) {
                this.playDoorSound(door.targetAngle > 0 ? 'open' : 'close');
            }
        }

        playDoorSound(type = 'open') {
            if (!this.audioEnabled) return;
            try {
                if (!this.audioCtx) {
                    this.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                }
                if (this.audioCtx.state === 'suspended') {
                    this.audioCtx.resume();
                }
                const now = this.audioCtx.currentTime;
                const osc = this.audioCtx.createOscillator();
                const gain = this.audioCtx.createGain();
                osc.connect(gain);
                gain.connect(this.audioCtx.destination);

                if (type === 'open') {
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(280, now);
                    osc.frequency.exponentialRampToValueAtTime(460, now + 0.14);
                    gain.gain.setValueAtTime(0.02, now);
                    gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.16);
                    osc.start(now);
                    osc.stop(now + 0.17);
                } else {
                    osc.type = 'triangle';
                    osc.frequency.setValueAtTime(420, now);
                    osc.frequency.exponentialRampToValueAtTime(240, now + 0.1);
                    gain.gain.setValueAtTime(0.025, now);
                    gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.12);
                    osc.start(now);
                    osc.stop(now + 0.13);
                }
            } catch (e) {
                // AudioContext not yet resumed
            }
        }

        handleKeyDown(e) {
            const activeTag = document.activeElement ? document.activeElement.tagName.toLowerCase() : '';
            const isInput = activeTag === 'input' || activeTag === 'textarea' || document.activeElement?.isContentEditable;
            if (isInput) return;

            if (!this.isPOVMode) return;

            const code = e.code;
            if (code === 'KeyW' || code === 'ArrowUp' ||
                code === 'KeyS' || code === 'ArrowDown' ||
                code === 'KeyA' || code === 'ArrowLeft' ||
                code === 'KeyD' || code === 'ArrowRight' ||
                code === 'ShiftLeft' || code === 'ShiftRight') {
                e.preventDefault();
                this.activeMovementKeys.add(code);
            } else if (code === 'KeyV') {
                e.preventDefault();
                this.togglePOVType();
            } else if (code === 'KeyE') {
                e.preventDefault();
                this.sitOrStandCurrentAgent();
            } else if (code === 'KeyF') {
                e.preventDefault();
                this.toggleNearestDoor();
            } else if (code === 'Escape') {
                e.preventDefault();
                this.exitAgentPOV();
            }
        }

        handleKeyUp(e) {
            const code = e.code;
            if (this.activeMovementKeys.has(code)) {
                this.activeMovementKeys.delete(code);
            }
        }

        updatePOVAndPlayerControls(delta, elapsed) {
            if (!this.isPOVMode || !this.povRoleKey || !this.camera) return;
            const sims = this.simsCharacters.get(this.povRoleKey);
            if (!sims) return;

            const charPos = sims.group.position;
            const keys = this.activeMovementKeys;

            let moveFwd = 0;
            let moveStrafe = 0;

            if (keys.has('KeyW') || keys.has('ArrowUp')) moveFwd += 1;
            if (keys.has('KeyS') || keys.has('ArrowDown')) moveFwd -= 1;
            if (keys.has('KeyA') || keys.has('ArrowLeft')) moveStrafe -= 1;
            if (keys.has('KeyD') || keys.has('ArrowRight')) moveStrafe += 1;

            const isMoving = moveFwd !== 0 || moveStrafe !== 0;
            const isSprinting = keys.has('ShiftLeft') || keys.has('ShiftRight');

            if (isMoving) {
                const isSeated = sims.currentState === 'AT_DESK' || sims.currentState === 'SEATED' || sims.currentState === 'ON_SOFA' || sims.currentState === 'AT_PANTRY' || sims.currentState === 'AT_MEETING';
                if (isSeated) {
                    this.releaseSeat(this.povRoleKey);
                    sims.currentChair = null;
                    this.setStandingPose(sims);
                }

                sims.currentState = 'WALKING';
                sims.targetWaypoint = null;

                const rot = sims.group.rotation.y;
                const fwdX = Math.sin(rot);
                const fwdZ = Math.cos(rot);
                const rightX = -Math.cos(rot);
                const rightZ = Math.sin(rot);

                const dirX = fwdX * moveFwd + rightX * moveStrafe;
                const dirZ = fwdZ * moveFwd + rightZ * moveStrafe;
                const len = Math.sqrt(dirX * dirX + dirZ * dirZ);

                if (len > 0.001) {
                    const normDirX = dirX / len;
                    const normDirZ = dirZ / len;
                    const speed = isSprinting ? 4.2 : 2.2;

                    const deltaX = normDirX * speed * delta;
                    const deltaZ = normDirZ * speed * delta;

                    const charRadius = 0.38;
                    let nextX = charPos.x + deltaX;
                    let nextZ = charPos.z + deltaZ;

                    // Sliding collision: test X and Z separately against frosted walls & closed doors
                    if (this.checkCollision(nextX, charPos.z, charRadius)) {
                        nextX = charPos.x; // Blocked along X, zero out X movement
                    }
                    if (this.checkCollision(nextX, nextZ, charRadius)) {
                        nextZ = charPos.z; // Blocked along Z, zero out Z movement
                    }

                    charPos.x = nextX;
                    charPos.z = nextZ;

                    const walkFreq = isSprinting ? 11.0 : 7.5;
                    const stride = Math.sin(elapsed * walkFreq) * 0.55;

                    if (sims.leftLeg) sims.leftLeg.rotation.x = stride;
                    if (sims.rightLeg) sims.rightLeg.rotation.x = -stride;
                    if (sims.leftKnee) sims.leftKnee.rotation.x = stride < 0 ? Math.abs(stride) * 1.15 : 0.08;
                    if (sims.rightKnee) sims.rightKnee.rotation.x = stride > 0 ? Math.abs(stride) * 1.15 : 0.08;
                    if (sims.leftFoot) sims.leftFoot.rotation.x = stride < 0 ? 0.2 : -0.1;
                    if (sims.rightFoot) sims.rightFoot.rotation.x = stride > 0 ? 0.2 : -0.1;

                    if (sims.leftArm) sims.leftArm.rotation.x = -stride * 0.55;
                    if (sims.rightArm) sims.rightArm.rotation.x = stride * 0.55;
                    if (sims.leftElbow) sims.leftElbow.rotation.x = 0.25 + Math.abs(stride) * 0.2;
                    if (sims.rightElbow) sims.rightElbow.rotation.x = 0.25 + Math.abs(stride) * 0.2;

                    if (sims.bodyPivot) {
                        sims.bodyPivot.position.y = Math.abs(Math.sin(elapsed * walkFreq)) * 0.035;
                        sims.bodyPivot.rotation.z = Math.sin(elapsed * walkFreq) * 0.025;
                        sims.bodyPivot.rotation.y = -Math.sin(elapsed * walkFreq) * 0.03;
                    }
                }
            } else if (sims.currentState === 'WALKING' && !sims.targetWaypoint) {
                sims.currentState = 'IDLE';
                this.setStandingPose(sims);
            }

            charPos.x = Math.max(-23.8, Math.min(23.8, charPos.x));
            charPos.z = Math.max(-14.8, Math.min(9.2, charPos.z));
            charPos.y = 0;

            const rot = sims.group.rotation.y;
            const isSitting = sims.currentState === 'AT_DESK' || sims.currentState === 'SEATED' || sims.currentState === 'ON_SOFA' || sims.currentState === 'AT_PANTRY' || sims.currentState === 'AT_MEETING';
            const pitch = this.povPitch || 0;

            if (this.povType === 'first_person') {
                const eyeY = isSitting ? 1.24 : 1.58;
                this.camera.position.set(charPos.x, eyeY, charPos.z);

                const lookDist = 5.0;
                const targetX = charPos.x + Math.sin(rot) * lookDist;
                const targetY = eyeY + Math.tan(pitch) * lookDist;
                const targetZ = charPos.z + Math.cos(rot) * lookDist;
                this.camera.lookAt(targetX, targetY, targetZ);

            } else {
                const camHeight = isSitting ? 1.45 : 1.75;
                const camDist = 2.4;
                const shoulderOffsetX = Math.cos(rot) * 0.35;
                const shoulderOffsetZ = -Math.sin(rot) * 0.35;

                const desiredCamX = charPos.x - Math.sin(rot) * camDist + shoulderOffsetX;
                const desiredCamY = camHeight;
                const desiredCamZ = charPos.z - Math.cos(rot) * camDist + shoulderOffsetZ;

                this.camera.position.set(desiredCamX, desiredCamY, desiredCamZ);

                const lookDist = 2.5;
                const lookTargetX = charPos.x + Math.sin(rot) * lookDist;
                const lookTargetY = (isSitting ? 1.15 : 1.45) + Math.tan(pitch) * lookDist;
                const lookTargetZ = charPos.z + Math.cos(rot) * lookDist;
                this.camera.lookAt(lookTargetX, lookTargetY, lookTargetZ);
            }
        }

        // ==========================================
        // 6. EVENT HANDLERS & RAYCASTING
        // ==========================================
        onResize() {
            if (!this.container || !this.renderer || !this.camera) return;
            const width = this.container.clientWidth || 900;
            const height = this.container.clientHeight || 640;
            this.camera.aspect = width / height;
            this.camera.updateProjectionMatrix();
            this.renderer.setSize(width, height);
            this.renderer.domElement.style.maxWidth = '100%';
            this.renderer.domElement.style.maxHeight = '100%';
        }

        onPointerMove(e) {
            if (this.isPOVMode && this.povRoleKey) {
                if (this.isMouseDown) {
                    const deltaX = e.clientX - this.prevMousePos.x;
                    const deltaY = e.clientY - this.prevMousePos.y;
                    this.prevMousePos = { x: e.clientX, y: e.clientY };

                    const sims = this.simsCharacters.get(this.povRoleKey);
                    if (sims) {
                        sims.group.rotation.y -= deltaX * 0.007;
                        this.povPitch = Math.max(-0.6, Math.min(0.6, (this.povPitch || 0) - deltaY * 0.005));
                    }
                }
                return;
            }

            const rect = this.renderer.domElement.getBoundingClientRect();
            this.mouse.x = ((e.clientX - rect.left) / rect.width) * 2 - 1;
            this.mouse.y = -((e.clientY - rect.top) / rect.height) * 2 + 1;

            if (this.interactiveObjects.length > 0 && this.camera) {
                this.raycaster.setFromCamera(this.mouse, this.camera);
                const intersects = this.raycaster.intersectObjects(this.interactiveObjects, true);
                if (intersects.length > 0) {
                    this.renderer.domElement.style.cursor = 'pointer';
                } else {
                    this.renderer.domElement.style.cursor = 'default';
                }
            }
        }

        onPointerClick(e) {
            if (this.isPOVMode) return;

            if (this._clickStartPos) {
                const dist = Math.hypot(e.clientX - this._clickStartPos.x, e.clientY - this._clickStartPos.y);
                if (dist > 8) return; // User was dragging/panning camera
            }

            const rect = this.renderer.domElement.getBoundingClientRect();
            this.mouse.x = ((e.clientX - rect.left) / rect.width) * 2 - 1;
            this.mouse.y = -((e.clientY - rect.top) / rect.height) * 2 + 1;

            this.raycaster.setFromCamera(this.mouse, this.camera);
            const intersects = this.raycaster.intersectObjects(this.interactiveObjects, true);

            if (intersects.length > 0) {
                let hitObj = intersects[0].object;

                // Check if an interactive glass door was clicked
                let doorId = hitObj.userData ? hitObj.userData.doorId : null;
                let curDoor = hitObj;
                while (!doorId && curDoor.parent && curDoor.parent !== this.scene) {
                    curDoor = curDoor.parent;
                    if (curDoor.userData && curDoor.userData.doorId) {
                        doorId = curDoor.userData.doorId;
                    }
                }
                if (doorId && this.doors.has(doorId)) {
                    this.toggleDoor(doorId);
                    return;
                }

                let roleKey = hitObj.userData ? hitObj.userData.roleKey : null;
                let agentData = hitObj.userData ? hitObj.userData.agentData : null;

                let cur = hitObj;
                while (!roleKey && cur.parent && cur.parent !== this.scene) {
                    cur = cur.parent;
                    if (cur.userData && cur.userData.roleKey) {
                        roleKey = cur.userData.roleKey;
                        agentData = cur.userData.agentData;
                    }
                }

                if (roleKey && typeof this.onSelectAgent === 'function') {
                    this.onSelectAgent(roleKey, agentData);
                    this.selectedAgentRole = roleKey;
                }
            }
        }

        // ==========================================
        // 7. ULTRA-REALISTIC ANIMATION LOOP
        // ==========================================
        updateUrbanEnvironment(delta, elapsed) {
            // 1. Dynamic Moving Vehicles
            if (this.cityVehicles && this.cityVehicles.length > 0) {
                for (let i = 0; i < this.cityVehicles.length; i++) {
                    const v = this.cityVehicles[i];
                    if (!v || !v.mesh) continue;

                    const moveDist = v.speed * delta * v.dirSign;
                    if (v.dirAxis === 'x') {
                        v.mesh.position.x += moveDist;
                        if (v.dirSign > 0 && v.mesh.position.x > v.maxCoord) {
                            v.mesh.position.x = v.minCoord;
                        } else if (v.dirSign < 0 && v.mesh.position.x < v.minCoord) {
                            v.mesh.position.x = v.maxCoord;
                        }
                    } else {
                        v.mesh.position.z += moveDist;
                        if (v.dirSign > 0 && v.mesh.position.z > v.maxCoord) {
                            v.mesh.position.z = v.minCoord;
                        } else if (v.dirSign < 0 && v.mesh.position.z < v.minCoord) {
                            v.mesh.position.z = v.maxCoord;
                        }
                    }
                }
            }

            // 2. Dynamic City & Terrace NPCs
            if (this.cityNPCs && this.cityNPCs.length > 0) {
                for (let i = 0; i < this.cityNPCs.length; i++) {
                    const npc = this.cityNPCs[i];
                    if (!npc || !npc.mesh) continue;

                    if (npc.role === 'walker' || npc.role === 'crosser' || npc.role === 'terrace_walker') {
                        // Walking locomotion
                        const step = npc.speed * delta * npc.dir;
                        if (npc.axis === 'x') {
                            npc.mesh.position.x += step;
                            if (npc.dir > 0 && npc.mesh.position.x > npc.max) {
                                npc.dir = -1;
                                npc.mesh.rotation.y = (npc.role === 'terrace_walker') ? -Math.PI / 2 : Math.PI / 2;
                            } else if (npc.dir < 0 && npc.mesh.position.x < npc.min) {
                                npc.dir = 1;
                                npc.mesh.rotation.y = (npc.role === 'terrace_walker') ? Math.PI / 2 : -Math.PI / 2;
                            }
                        } else {
                            npc.mesh.position.z += step;
                            if (npc.dir > 0 && npc.mesh.position.z > npc.max) {
                                npc.dir = -1;
                                npc.mesh.rotation.y = Math.PI;
                            } else if (npc.dir < 0 && npc.mesh.position.z < npc.min) {
                                npc.dir = 1;
                                npc.mesh.rotation.y = 0;
                            }
                        }

                        // Leg and arm swing animation
                        const swing = Math.sin(elapsed * (npc.speed * 4.2) + npc.seed) * 0.45;
                        if (npc.leftLeg) npc.leftLeg.rotation.x = swing;
                        if (npc.rightLeg) npc.rightLeg.rotation.x = -swing;
                        if (npc.leftArm) npc.leftArm.rotation.x = -swing * 0.75;
                        if (npc.rightArm) npc.rightArm.rotation.x = swing * 0.75;

                    } else if (npc.role === 'conversing' || npc.role === 'terrace_conversing') {
                        // Natural head nod & hand gestures during conversation
                        const nod = Math.sin(elapsed * 2.2 + npc.seed) * 0.08;
                        const tilt = Math.cos(elapsed * 1.5 + npc.seed) * 0.05;
                        if (npc.head) npc.head.rotation.set(nod, tilt, 0);

                        if (npc.rightArm && !npc.action) {
                            const gesture = Math.sin(elapsed * 1.8 + npc.seed) * 0.18;
                            npc.rightArm.rotation.x = -0.5 + gesture;
                        }

                    } else if (npc.role === 'terrace_sightseer') {
                        // Subtle breathing & slow scenic panning
                        const pan = Math.sin(elapsed * 0.4 + npc.seed) * 0.18;
                        if (npc.head) npc.head.rotation.y = pan;

                    } else if (npc.role === 'waiting') {
                        // Subtle idle weight shift & head turning
                        const glance = Math.sin(elapsed * 0.8 + npc.seed) * 0.25;
                        if (npc.head) npc.head.rotation.y = glance;
                    }
                }
            }

            // 3. Sky Helicopter Flight Patrol Dynamics
            if (this.skyHelicopter) {
                this.helicopterFlightTimer = (this.helicopterFlightTimer || 0) + delta * 0.18;
                const angle = this.helicopterFlightTimer;

                // Elliptical flight perimeter around skyline: X-radius: 96m, Z-radius: 88m
                const radiusX = 96.0;
                const radiusZ = 88.0;

                const posX = Math.cos(angle) * radiusX;
                const posZ = Math.sin(angle) * radiusZ;
                const posY = 23.5 + Math.sin(angle * 2.5) * 2.2;

                this.skyHelicopter.position.set(posX, posY, posZ);

                // Tangent velocity heading direction
                const vx = -Math.sin(angle) * radiusX;
                const vz = Math.cos(angle) * radiusZ;
                const targetYaw = Math.atan2(vx, vz);

                this.skyHelicopter.rotation.y = targetYaw;

                // Banked turn roll
                this.skyHelicopter.rotation.z = -0.14 * Math.sin(angle);
                // Slight forward aerodynamic pitch
                this.skyHelicopter.rotation.x = 0.04;

                // High-speed rotor spin
                if (this.mainRotorMesh) {
                    this.mainRotorMesh.rotation.y += delta * 32.0;
                }
                if (this.tailRotorMesh) {
                    this.tailRotorMesh.rotation.x += delta * 46.0;
                }

                // Strobe anticollision beacon flash (sharp double pulse every 1.5s)
                if (this.heliStrobeLight) {
                    const cycle = elapsed % 1.5;
                    const flash = (cycle < 0.08) || (cycle > 0.18 && cycle < 0.26);
                    this.heliStrobeLight.visible = flash;
                }
            }
        }

        animate() {
            requestAnimationFrame(() => this.animate());

            const delta = this.clock.getDelta();
            const elapsed = this.clock.getElapsedTime();

            // Update live working monitors across all workstations (KPIs, pipelines, charts, scrolling code)
            this.updateLiveMonitorScreens(elapsed, delta);

            // Dynamic Urban Environment Animation (Traffic, NPCs, Sky Helicopter Patrol)
            this.updateUrbanEnvironment(delta, elapsed);

            // Pulse / blink aviation obstruction beacons on skyscrapers
            if (this.blinkingLeds && this.blinkingLeds.length > 0) {
                const flash = Math.sin(elapsed * 4.0) > 0.0;
                for (let i = 0; i < this.blinkingLeds.length; i++) {
                    const b = this.blinkingLeds[i];
                    if (b) {
                        b.visible = this.isNight ? flash : (flash && (i % 2 === 0));
                    }
                }
            }

            // Smooth camera glide (only when NOT in POV mode)
            if (!this.isPOVMode && this.isLerpingCamera && this.targetCameraPos && this.targetLookAt) {
                this.camera.position.lerp(this.targetCameraPos, 0.08);
                this.controls.target.lerp(this.targetLookAt, 0.08);
                if (this.camera.position.distanceTo(this.targetCameraPos) < 0.2) {
                    this.isLerpingCamera = false;
                }
            }

            // Update interactive glass doors (swing animation & proximity detection)
            this.updateDoors(delta);

            // POV Direct Player Control Camera & Motion Update
            if (this.isPOVMode && this.povRoleKey) {
                this.updatePOVAndPlayerControls(delta, elapsed);
            }

            // DYNAMIC AGENT-TO-AGENT MUTUAL SEPARATION (ANTI-OVERLAP)
            // ONLY applies if BOTH agents are walking! Never displace seated agents!
            const agentList = Array.from(this.simsCharacters.values());
            const minDist = 1.15; // 1.15m comfort radius
            for (let i = 0; i < agentList.length; i++) {
                const a1 = agentList[i];
                if (a1.currentState !== 'WALKING') continue;
                const p1 = a1.group.position;
                for (let j = i + 1; j < agentList.length; j++) {
                    const a2 = agentList[j];
                    if (a2.currentState !== 'WALKING') continue;
                    const p2 = a2.group.position;
                    const dx = p1.x - p2.x;
                    const dz = p1.z - p2.z;
                    const distSq = dx * dx + dz * dz;

                    if (distSq < minDist * minDist && distSq > 0.0001) {
                        const dist = Math.sqrt(distSq);
                        const overlap = (minDist - dist) * 0.5;
                        const nx = dx / dist;
                        const nz = dz / dist;

                        p1.x += nx * overlap * 0.25;
                        p1.z += nz * overlap * 0.25;
                        p2.x -= nx * overlap * 0.25;
                        p2.z -= nz * overlap * 0.25;
                    }
                }
            }

            this.simsCharacters.forEach((sims) => {
                // If this agent is controlled in POV mode, skip autonomous AI routines
                if (this.isPOVMode && this.povRoleKey === sims.roleKey) {
                    return;
                }

                // HARD POSITION & ROTATION LOCK FOR SEATED AGENTS (PREVENTS DRIFT, TILT, OR INVERSION)
                if (sims.currentState === 'AT_DESK') {
                    sims.group.position.set(sims.homePos.x, 0, sims.homePos.z);
                    sims.group.rotation.set(0, sims.homePos.rot, 0);
                    if (sims.leftLeg) sims.leftLeg.rotation.set(-Math.PI / 2, 0, 0);
                    if (sims.rightLeg) sims.rightLeg.rotation.set(-Math.PI / 2, 0, 0);
                    if (sims.leftKnee) sims.leftKnee.rotation.set(Math.PI / 2, 0, 0);
                    if (sims.rightKnee) sims.rightKnee.rotation.set(Math.PI / 2, 0, 0);
                    if (sims.leftFoot) sims.leftFoot.rotation.set(0, 0, 0);
                    if (sims.rightFoot) sims.rightFoot.rotation.set(0, 0, 0);
                } else if (sims.currentChair && (sims.currentState === 'SEATED' || sims.currentState === 'ON_SOFA' || sims.currentState === 'AT_PANTRY' || sims.currentState === 'AT_MEETING')) {
                    sims.group.position.set(sims.currentChair.pos.x, 0, sims.currentChair.pos.z);
                    sims.group.rotation.set(0, sims.currentChair.rot, 0);
                    if (sims.leftLeg) sims.leftLeg.rotation.set(-Math.PI / 2, 0, 0);
                    if (sims.rightLeg) sims.rightLeg.rotation.set(-Math.PI / 2, 0, 0);
                    if (sims.leftKnee) sims.leftKnee.rotation.set(Math.PI / 2, 0, 0);
                    if (sims.rightKnee) sims.rightKnee.rotation.set(Math.PI / 2, 0, 0);
                    if (sims.leftFoot) sims.leftFoot.rotation.set(0, 0, 0);
                    if (sims.rightFoot) sims.rightFoot.rotation.set(0, 0, 0);
                }
                // Plumbob subtle floating and rotation (anchored locally inside bodyPivot)
                if (sims.plumbob) {
                    sims.plumbob.rotation.y += 0.035;
                    sims.plumbob.position.y = 2.05 + Math.sin(elapsed * 2.8 + sims.homePos.x) * 0.04;
                }

                // Breathing Cycle: continuous subtle expansion and contraction of chest
                const breath = Math.sin(elapsed * 2.2 + sims.homePos.x * 2) * 0.015;
                if (sims.torso) {
                    sims.torso.scale.set(1 + breath, 1 + breath * 0.8, 1 + breath * 1.4);
                }

                // DYNAMIC HUMAN DESK ACTIVITIES (TYPING, MOUSING, COFFEE, INSPECTING, PONDERING)
                if (sims.currentState === 'AT_DESK') {
                    const now = Date.now();
                    if (!sims.nextActivityChange || now > sims.nextActivityChange) {
                        if (sims.isTaskActive) {
                            sims.deskActivity = Math.random() < 0.75 ? 'TYPING' : 'MOUSING';
                            sims.nextActivityChange = now + 4000 + Math.random() * 5000;
                        } else {
                            const actRoll = Math.random();
                            if (actRoll < 0.38) sims.deskActivity = 'TYPING';
                            else if (actRoll < 0.65) sims.deskActivity = 'MOUSING';
                            else if (actRoll < 0.78) sims.deskActivity = 'INSPECTING';
                            else if (actRoll < 0.90) sims.deskActivity = 'PONDERING';
                            else {
                                sims.deskActivity = 'COFFEE';
                                sims.coffeeTimer = elapsed;
                            }
                            sims.nextActivityChange = now + 3500 + Math.random() * 5500;
                        }
                    }

                    const act = sims.deskActivity || 'TYPING';

                    if (act === 'TYPING') {
                        // High-speed natural keystrokes & wrist flutter on mechanical keyboard
                        const typeWave1 = Math.sin(elapsed * 13 + sims.homePos.x * 4);
                        const typeWave2 = Math.cos(elapsed * 12 + sims.homePos.z * 4);

                        if (sims.bodyPivot) {
                            sims.bodyPivot.position.y = -0.36;
                            sims.bodyPivot.rotation.set(0.02, 0, 0);
                        }
                        if (sims.leftArm) sims.leftArm.rotation.set(-Math.PI / 3.7 + typeWave1 * 0.045, 0, -0.08);
                        if (sims.rightArm) sims.rightArm.rotation.set(-Math.PI / 3.7 + typeWave2 * 0.045, 0, 0.08);
                        if (sims.leftElbow) sims.leftElbow.rotation.set(Math.PI / 3.9 + Math.abs(typeWave1) * 0.06, 0, 0);
                        if (sims.rightElbow) sims.rightElbow.rotation.set(Math.PI / 3.9 + Math.abs(typeWave2) * 0.06, 0, 0);
                        if (sims.head) sims.head.rotation.set(0.08 + Math.sin(elapsed * 3) * 0.02, Math.sin(elapsed * 0.8) * 0.04, 0);

                    } else if (act === 'MOUSING') {
                        // Left hand resting comfortably on keyboard / desk pad
                        if (sims.bodyPivot) {
                            sims.bodyPivot.position.y = -0.36;
                            sims.bodyPivot.rotation.set(0, 0, 0);
                        }
                        if (sims.leftArm) sims.leftArm.rotation.set(-Math.PI / 3.8, 0, -0.06);
                        if (sims.leftElbow) sims.leftElbow.rotation.set(Math.PI / 4, 0, 0);

                        // Right arm moving optical mouse with subtle micro-ellipses & clicks
                        const mouseX = Math.sin(elapsed * 2.8 + sims.homePos.x) * 0.035;
                        const mouseZ = Math.cos(elapsed * 3.4 + sims.homePos.z) * 0.025;
                        if (sims.rightArm) sims.rightArm.rotation.set(-Math.PI / 3.6 + mouseZ, mouseX * 0.6, 0.14);
                        if (sims.rightElbow) sims.rightElbow.rotation.set(Math.PI / 4.1 + Math.abs(mouseZ), 0, mouseX);

                        // Head gaze panning between Primary 27" Monitor and Secondary 24" Monitor
                        const gazePan = Math.sin(elapsed * 1.5 + sims.homePos.x);
                        if (sims.head) sims.head.rotation.set(0.04, gazePan > 0.2 ? 0.26 : -0.04, 0);

                    } else if (act === 'COFFEE') {
                        // Realistic 4-second sipping kinematic sequence
                        const t = elapsed - (sims.coffeeTimer || elapsed);
                        if (sims.bodyPivot) sims.bodyPivot.position.y = -0.36;
                        if (sims.leftArm) sims.leftArm.rotation.set(-Math.PI / 4, 0, -0.06);
                        if (sims.leftElbow) sims.leftElbow.rotation.set(Math.PI / 4.5, 0, 0);

                        if (t <= 1.2) {
                            // Lifting coffee mug to lips
                            const p = t / 1.2;
                            if (sims.rightArm) sims.rightArm.rotation.set(-Math.PI / 3.8 - p * 0.65, -p * 0.25, 0.08 + p * 0.2);
                            if (sims.rightElbow) sims.rightElbow.rotation.set(Math.PI / 4 + p * 0.85, 0, -p * 0.3);
                            if (sims.head) sims.head.rotation.set(0.06 - p * 0.18, 0, 0);
                        } else if (t <= 2.8) {
                            // Sipping with subtle throat movement & head tilt back
                            if (sims.rightArm) sims.rightArm.rotation.set(-Math.PI / 2.1, -0.25, 0.28);
                            if (sims.rightElbow) sims.rightElbow.rotation.set(Math.PI / 2.3 + Math.sin(elapsed * 4) * 0.03, 0, -0.3);
                            if (sims.head) sims.head.rotation.set(-0.16 + Math.sin(elapsed * 2.5) * 0.02, 0, 0);
                        } else if (t <= 4.0) {
                            // Lowering mug back down to felt pad
                            const p = Math.max(0, (4.0 - t) / 1.2);
                            if (sims.rightArm) sims.rightArm.rotation.set(-Math.PI / 3.8 - p * 0.65, -p * 0.25, 0.08 + p * 0.2);
                            if (sims.rightElbow) sims.rightElbow.rotation.set(Math.PI / 4 + p * 0.85, 0, -p * 0.3);
                            if (sims.head) sims.head.rotation.set(0.06 - p * 0.18, 0, 0);
                        } else {
                            sims.deskActivity = 'TYPING';
                        }

                    } else if (act === 'INSPECTING') {
                        // Leaning forward over desk, propping chin in hand, inspecting dashboard
                        if (sims.bodyPivot) {
                            sims.bodyPivot.position.y = -0.36;
                            sims.bodyPivot.rotation.set(0.08, 0, 0);
                        }
                        if (sims.leftArm) sims.leftArm.rotation.set(-Math.PI / 3.5, 0, -0.12);
                        if (sims.leftElbow) sims.leftElbow.rotation.set(Math.PI / 3.4, 0, 0);

                        // Hand under chin
                        if (sims.rightArm) sims.rightArm.rotation.set(-Math.PI / 2.3, -0.16, 0.24);
                        if (sims.rightElbow) sims.rightElbow.rotation.set(Math.PI / 1.85, 0, -0.25);

                        // Subtle discerning nod
                        const nod = Math.sin(elapsed * 1.8 + sims.homePos.x) * 0.04;
                        if (sims.head) sims.head.rotation.set(0.08 + nod, 0.05, 0.03);

                    } else if (act === 'PONDERING') {
                        // Reclined posture against mesh backrest, contemplative gaze upwards
                        if (sims.bodyPivot) {
                            sims.bodyPivot.position.y = -0.36;
                            sims.bodyPivot.rotation.set(-0.07, 0, 0);
                        }
                        if (sims.leftArm) sims.leftArm.rotation.set(-Math.PI / 5.2, 0, -0.15);
                        if (sims.rightArm) sims.rightArm.rotation.set(-Math.PI / 5.2, 0, 0.15);
                        if (sims.leftElbow) sims.leftElbow.rotation.set(Math.PI / 4.8, 0, 0);
                        if (sims.rightElbow) sims.rightElbow.rotation.set(Math.PI / 4.8, 0, 0);
                        if (sims.head) sims.head.rotation.set(-0.14, Math.sin(elapsed * 0.6 + sims.homePos.x) * 0.08, 0);
                    }
                }

                // Super-Realistic Kinematic Walking Gait
                if (sims.currentState === 'WALKING' && sims.targetWaypoint) {
                    const charPos = sims.group.position;
                    const dir = new THREE.Vector3().subVectors(sims.targetWaypoint, charPos);
                    dir.y = 0;
                    const dist = dir.length();

                    if (dist > 0.35) {
                        // Doorway check: if NPC is approaching a door that is not yet open, pause momentarily until it opens
                        let isWaitingForDoor = false;
                        if (this.doors && this.doors.size > 0) {
                            for (const door of this.doors.values()) {
                                const dToDoor = charPos.distanceTo(door.center);
                                if (dToDoor < 1.9 && door.currentAngle < 0.38) {
                                    const toDoorDir = new THREE.Vector3().subVectors(door.center, charPos);
                                    toDoorDir.y = 0;
                                    if (dir.dot(toDoorDir) > 0) {
                                        isWaitingForDoor = true;
                                        door.targetAngle = door.maxAngle; // Trigger door opening immediately
                                        break;
                                    }
                                }
                            }
                        }

                        if (isWaitingForDoor) {
                            // Stand gracefully waiting for door to swing open
                            sims.group.lookAt(charPos.x + dir.x, 0, charPos.z + dir.z);
                            if (sims.leftLeg) sims.leftLeg.rotation.x = 0;
                            if (sims.rightLeg) sims.rightLeg.rotation.x = 0;
                            if (sims.leftArm) sims.leftArm.rotation.x = 0;
                            if (sims.rightArm) sims.rightArm.rotation.x = 0;
                        } else {
                            dir.normalize();
                            charPos.addScaledVector(dir, sims.walkSpeed * delta);
                            sims.group.lookAt(charPos.x + dir.x, 0, charPos.z + dir.z);

                            const walkFreq = 7.5;
                            const stride = Math.sin(elapsed * walkFreq) * 0.55;

                        // Hip swinging
                        if (sims.leftLeg) sims.leftLeg.rotation.x = stride;
                        if (sims.rightLeg) sims.rightLeg.rotation.x = -stride;

                        // Knee flexion: knees flex backward when foot is lifted
                        if (sims.leftKnee) sims.leftKnee.rotation.x = stride < 0 ? Math.abs(stride) * 1.15 : 0.08;
                        if (sims.rightKnee) sims.rightKnee.rotation.x = stride > 0 ? Math.abs(stride) * 1.15 : 0.08;

                        // Feet articulation
                        if (sims.leftFoot) sims.leftFoot.rotation.x = stride < 0 ? 0.2 : -0.1;
                        if (sims.rightFoot) sims.rightFoot.rotation.x = stride > 0 ? 0.2 : -0.1;

                        // Counter-swinging arms with natural elbow bend
                        if (sims.leftArm) sims.leftArm.rotation.x = -stride * 0.55;
                        if (sims.rightArm) sims.rightArm.rotation.x = stride * 0.55;
                        if (sims.leftElbow) sims.leftElbow.rotation.x = 0.25 + Math.abs(stride) * 0.2;
                        if (sims.rightElbow) sims.rightElbow.rotation.x = 0.25 + Math.abs(stride) * 0.2;

                        // Pelvic bounce and torso sway
                        if (sims.bodyPivot) {
                            sims.bodyPivot.position.y = Math.abs(Math.sin(elapsed * walkFreq)) * 0.035;
                            sims.bodyPivot.rotation.z = Math.sin(elapsed * walkFreq) * 0.025;
                            sims.bodyPivot.rotation.y = -Math.sin(elapsed * walkFreq) * 0.03;
                        }
                    }
                } else {
                        // Advance to next waypoint along corridor path
                        sims.currentPathIndex = (sims.currentPathIndex || 0) + 1;
                        if (sims.pathWaypoints && sims.currentPathIndex < sims.pathWaypoints.length) {
                            sims.targetWaypoint = sims.pathWaypoints[sims.currentPathIndex];
                        } else {
                            // Arrived at destination
                            sims.targetWaypoint = null;
                            if (typeof sims.arrivalCallback === 'function') {
                                const cb = sims.arrivalCallback;
                                sims.arrivalCallback = null;
                                cb();
                            }
                        }
                    }
                }

                // STRICT INTERIOR BOUNDARY CLAMPING
                // Under no circumstances can character exit building: X: [-24.0, 24.0], Z: [-15.0, 9.5], Y = 0
                sims.group.position.x = Math.max(-24.0, Math.min(24.0, sims.group.position.x));
                sims.group.position.z = Math.max(-15.0, Math.min(9.5, sims.group.position.z));
                sims.group.position.y = 0;
            });

            if (this.controls && !this.isPOVMode) {
                this.controls.update();
            }

            if (this.renderer && this.scene && this.camera) {
                this.renderer.render(this.scene, this.camera);
            }
        }

        destroy() {
            if (typeof window !== 'undefined') {
                window.removeEventListener('resize', this._onResize);
                window.removeEventListener('keydown', this._onKeyDown);
                window.removeEventListener('keyup', this._onKeyUp);
                window.removeEventListener('mouseup', this._onMouseUp);
                if (this._onThemeChanged) {
                    window.removeEventListener('cooca-theme-changed', this._onThemeChanged);
                }
            }
            if (this._themeObserver) {
                this._themeObserver.disconnect();
            }
            if (this.renderer && this.renderer.domElement) {
                this.renderer.domElement.removeEventListener('mousemove', this._onPointerMove);
                this.renderer.domElement.removeEventListener('click', this._onPointerClick);
                this.renderer.domElement.removeEventListener('mousedown', this._onMouseDown);
            }
            if (this.controls) {
                this.controls.dispose();
            }
            if (this.pollInterval) {
                clearInterval(this.pollInterval);
                this.pollInterval = null;
            }
            this.cityVehicles = [];
            this.cityNPCs = [];
            this.skyHelicopter = null;
            this.mainRotorMesh = null;
            this.tailRotorMesh = null;
            this.heliStrobeLight = null;
            if (this.doors) {
                this.doors.clear();
            }
            this.wallColliders = [];
        }
    }

    window.Cooca3DOffice = Cooca3DOffice;
})(window);

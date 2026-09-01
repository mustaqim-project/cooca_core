import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../providers/auth_provider.dart';

class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key});

  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen>
    with SingleTickerProviderStateMixin {
  final _pageCtrl = PageController();
  int _step = 0; // 0 = akun user, 1 = setup bisnis

  // Step 1 - Akun
  final _nameCtrl    = TextEditingController();
  final _emailCtrl   = TextEditingController();
  final _phoneCtrl   = TextEditingController();
  final _passCtrl    = TextEditingController();
  final _confirmCtrl = TextEditingController();
  bool _obscure1 = true, _obscure2 = true;
  final _step1Key = GlobalKey<FormState>();

  // Step 2 - Bisnis
  final _bizNameCtrl = TextEditingController();
  final _bizCityCtrl = TextEditingController();
  String _bizType    = 'retail';
  final _step2Key    = GlobalKey<FormState>();

  late AnimationController _animCtrl;
  late Animation<double>   _fadeAnim;

  final List<Map<String, dynamic>> _bizTypes = [
    {'value': 'retail',      'label': 'Retail / Toko',    'icon': Icons.store_rounded},
    {'value': 'fnb',         'label': 'F&B / Kuliner',    'icon': Icons.restaurant_rounded},
    {'value': 'service',     'label': 'Jasa / Service',   'icon': Icons.build_rounded},
    {'value': 'manufacture', 'label': 'Manufaktur',       'icon': Icons.factory_rounded},
    {'value': 'wholesale',   'label': 'Distributor',      'icon': Icons.local_shipping_rounded},
    {'value': 'other',       'label': 'Lainnya',          'icon': Icons.grid_view_rounded},
  ];

  @override
  void initState() {
    super.initState();
    _animCtrl = AnimationController(vsync: this, duration: const Duration(milliseconds: 400));
    _fadeAnim = CurvedAnimation(parent: _animCtrl, curve: Curves.easeOut);
    _animCtrl.forward();
  }

  @override
  void dispose() {
    _animCtrl.dispose();
    _pageCtrl.dispose();
    _nameCtrl.dispose(); _emailCtrl.dispose(); _phoneCtrl.dispose();
    _passCtrl.dispose(); _confirmCtrl.dispose();
    _bizNameCtrl.dispose(); _bizCityCtrl.dispose();
    super.dispose();
  }

  void _nextStep() {
    if (!(_step1Key.currentState?.validate() ?? false)) return;
    setState(() => _step = 1);
    _pageCtrl.nextPage(
      duration: const Duration(milliseconds: 350),
      curve: Curves.easeInOut,
    );
  }

  Future<void> _handleRegister() async {
    if (!(_step2Key.currentState?.validate() ?? false)) return;

    final auth = context.read<AuthProvider>();
    final ok = await auth.register(
      name: _nameCtrl.text.trim(),
      email: _emailCtrl.text.trim(),
      phone: _phoneCtrl.text.trim(),
      password: _passCtrl.text,
      businessName: _bizNameCtrl.text.trim(),
      businessType: _bizType,
      city: _bizCityCtrl.text.trim(),
    );

    if (!mounted) return;
    if (ok) {
      Navigator.of(context).pushReplacementNamed('/home');
    } else {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        content: Text(auth.errorMessage ?? 'Registrasi gagal. Coba lagi.'),
        backgroundColor: AppColors.danger,
      ));
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final isLoading = auth.status == AuthStatus.authenticating;

    return Scaffold(
      backgroundColor: AppColors.background,
      body: FadeTransition(
        opacity: _fadeAnim,
        child: SafeArea(
          child: Column(
            children: [
              // Header
              Padding(
                padding: const EdgeInsets.fromLTRB(20, 16, 20, 0),
                child: Row(
                  children: [
                    if (_step > 0)
                      IconButton(
                        icon: const Icon(Icons.arrow_back_rounded, color: AppColors.textSecondary),
                        onPressed: () {
                          setState(() => _step = 0);
                          _pageCtrl.previousPage(
                            duration: const Duration(milliseconds: 350),
                            curve: Curves.easeInOut,
                          );
                        },
                      )
                    else
                      IconButton(
                        icon: const Icon(Icons.arrow_back_rounded, color: AppColors.textSecondary),
                        onPressed: () => Navigator.of(context).pop(),
                      ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            _step == 0 ? 'Buat Akun Baru' : 'Setup Bisnis',
                            style: GoogleFonts.plusJakartaSans(
                              fontSize: 18, fontWeight: FontWeight.w800,
                              color: AppColors.textPrimary,
                            ),
                          ),
                          Text(
                            'Langkah ${_step + 1} dari 2',
                            style: GoogleFonts.plusJakartaSans(
                              fontSize: 12, color: AppColors.textMuted,
                            ),
                          ),
                        ],
                      ),
                    ),
                    // Step indicator
                    Row(
                      children: List.generate(2, (i) => AnimatedContainer(
                        duration: const Duration(milliseconds: 300),
                        width: i == _step ? 24 : 8,
                        height: 8,
                        margin: const EdgeInsets.only(left: 4),
                        decoration: BoxDecoration(
                          color: i <= _step ? AppColors.primary : AppColors.surfaceElevated,
                          borderRadius: BorderRadius.circular(4),
                        ),
                      )),
                    ),
                  ],
                ),
              ),

              // Progress bar
              Padding(
                padding: const EdgeInsets.fromLTRB(20, 16, 20, 0),
                child: LinearProgressIndicator(
                  value: (_step + 1) / 2,
                  backgroundColor: AppColors.surfaceElevated,
                  valueColor: const AlwaysStoppedAnimation<Color>(AppColors.primary),
                  borderRadius: BorderRadius.circular(4),
                  minHeight: 4,
                ),
              ),

              // Pages
              Expanded(
                child: PageView(
                  controller: _pageCtrl,
                  physics: const NeverScrollableScrollPhysics(),
                  children: [
                    _buildStep1(),
                    _buildStep2(auth, isLoading),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildStep1() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(24),
      child: Form(
        key: _step1Key,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const SizedBox(height: 8),
            // Logo
            Center(
              child: Container(
                width: 64, height: 64,
                decoration: BoxDecoration(
                  gradient: AppColors.logoGradient,
                  borderRadius: BorderRadius.circular(18),
                  boxShadow: [BoxShadow(color: AppColors.primary.withValues(alpha: 0.3), blurRadius: 24, offset: const Offset(0, 8))],
                ),
                child: const Icon(Icons.inventory_2_rounded, size: 32, color: Colors.white),
              ),
            ),
            const SizedBox(height: 24),

            _buildGlassCard(children: [
              _buildFieldLabel('Nama Lengkap'),
              _buildInput(controller: _nameCtrl, hint: 'Ahmad Rizky', icon: Icons.person_outline_rounded,
                  validator: (v) => (v == null || v.isEmpty) ? 'Nama wajib diisi' : null),
              const SizedBox(height: 14),

              _buildFieldLabel('Email'),
              _buildInput(controller: _emailCtrl, hint: 'email@contoh.com', icon: Icons.email_outlined,
                  type: TextInputType.emailAddress,
                  validator: (v) => (v == null || !v.contains('@')) ? 'Email tidak valid' : null),
              const SizedBox(height: 14),

              _buildFieldLabel('Nomor HP'),
              _buildInput(controller: _phoneCtrl, hint: '08xxxxxxxxxx', icon: Icons.phone_outlined,
                  type: TextInputType.phone,
                  validator: (v) => (v == null || v.length < 10) ? 'Nomor HP minimal 10 digit' : null),
              const SizedBox(height: 14),

              _buildFieldLabel('Password'),
              _buildInput(
                controller: _passCtrl, hint: 'Min. 8 karakter', icon: Icons.lock_outline_rounded,
                obscure: _obscure1,
                suffix: IconButton(
                  icon: Icon(_obscure1 ? Icons.visibility_off_outlined : Icons.visibility_outlined,
                    color: AppColors.textMuted, size: 20),
                  onPressed: () => setState(() => _obscure1 = !_obscure1),
                ),
                validator: (v) => (v == null || v.length < 8) ? 'Password minimal 8 karakter' : null,
              ),
              const SizedBox(height: 14),

              _buildFieldLabel('Konfirmasi Password'),
              _buildInput(
                controller: _confirmCtrl, hint: 'Ulangi password', icon: Icons.lock_outline_rounded,
                obscure: _obscure2,
                suffix: IconButton(
                  icon: Icon(_obscure2 ? Icons.visibility_off_outlined : Icons.visibility_outlined,
                    color: AppColors.textMuted, size: 20),
                  onPressed: () => setState(() => _obscure2 = !_obscure2),
                ),
                validator: (v) => (v != _passCtrl.text) ? 'Password tidak cocok' : null,
              ),
            ]),

            const SizedBox(height: 24),
            SizedBox(
              width: double.infinity, height: 52,
              child: ElevatedButton(
                onPressed: _nextStep,
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.primary,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                  elevation: 0,
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Text('Lanjutkan', style: GoogleFonts.plusJakartaSans(
                      color: Colors.white, fontSize: 15, fontWeight: FontWeight.w700,
                    )),
                    const SizedBox(width: 8),
                    const Icon(Icons.arrow_forward_rounded, color: Colors.white, size: 18),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),
            Center(child: Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Text('Sudah punya akun? ', style: GoogleFonts.plusJakartaSans(fontSize: 13, color: AppColors.textMuted)),
                GestureDetector(
                  onTap: () => Navigator.of(context).pushReplacementNamed('/login'),
                  child: Text('Masuk', style: GoogleFonts.plusJakartaSans(fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.primaryLight)),
                ),
              ],
            )),
          ],
        ),
      ),
    );
  }

  Widget _buildStep2(AuthProvider auth, bool isLoading) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(24),
      child: Form(
        key: _step2Key,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const SizedBox(height: 8),
            Text('Hampir selesai! Lengkapi data bisnis Anda.',
              style: GoogleFonts.plusJakartaSans(fontSize: 14, color: AppColors.textMuted)),
            const SizedBox(height: 20),

            _buildGlassCard(children: [
              _buildFieldLabel('Nama Bisnis'),
              _buildInput(controller: _bizNameCtrl, hint: 'Toko Maju Jaya', icon: Icons.business_rounded,
                  validator: (v) => (v == null || v.isEmpty) ? 'Nama bisnis wajib diisi' : null),
              const SizedBox(height: 14),
              _buildFieldLabel('Kota / Lokasi'),
              _buildInput(controller: _bizCityCtrl, hint: 'Bandung', icon: Icons.location_on_outlined,
                  type: TextInputType.text,
                  validator: (v) => (v == null || v.isEmpty) ? 'Kota wajib diisi' : null),
            ]),

            const SizedBox(height: 20),
            Text('Jenis Bisnis', style: GoogleFonts.plusJakartaSans(
              fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.textMuted,
              letterSpacing: 0.3,
            )),
            const SizedBox(height: 10),

            // Business type grid
            GridView.count(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              crossAxisCount: 3,
              mainAxisSpacing: 10,
              crossAxisSpacing: 10,
              childAspectRatio: 1.1,
              children: _bizTypes.map((t) {
                final isSelected = _bizType == t['value'];
                return GestureDetector(
                  onTap: () => setState(() => _bizType = t['value']),
                  child: AnimatedContainer(
                    duration: const Duration(milliseconds: 200),
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color: isSelected ? AppColors.primaryGlow : AppColors.surfaceDeep,
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(
                        color: isSelected ? AppColors.primary : AppColors.border,
                        width: isSelected ? 2 : 1,
                      ),
                    ),
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(t['icon'] as IconData,
                          size: 24,
                          color: isSelected ? AppColors.primary : AppColors.textMuted,
                        ),
                        const SizedBox(height: 6),
                        Text(
                          t['label'],
                          textAlign: TextAlign.center,
                          style: GoogleFonts.plusJakartaSans(
                            fontSize: 10, fontWeight: FontWeight.w600,
                            color: isSelected ? AppColors.primaryLight : AppColors.textMuted,
                          ),
                        ),
                      ],
                    ),
                  ),
                );
              }).toList(),
            ),

            const SizedBox(height: 24),
            SizedBox(
              width: double.infinity, height: 52,
              child: ElevatedButton(
                onPressed: isLoading ? null : _handleRegister,
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.primary,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                  elevation: 0,
                ),
                child: isLoading
                    ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2.5))
                    : Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          const Icon(Icons.check_circle_outline_rounded, color: Colors.white, size: 18),
                          const SizedBox(width: 8),
                          Text('Buat Akun & Mulai', style: GoogleFonts.plusJakartaSans(
                            color: Colors.white, fontSize: 15, fontWeight: FontWeight.w700,
                          )),
                        ],
                      ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildGlassCard({required List<Widget> children}) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: AppColors.glassCard,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: AppColors.glassBorder),
      ),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: children),
    );
  }

  Widget _buildFieldLabel(String label) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 6),
      child: Text(label, style: GoogleFonts.plusJakartaSans(
        fontSize: 12, fontWeight: FontWeight.w600,
        color: AppColors.textMuted, letterSpacing: 0.3,
      )),
    );
  }

  Widget _buildInput({
    required TextEditingController controller,
    required String hint,
    required IconData icon,
    TextInputType? type,
    bool obscure = false,
    String? Function(String?)? validator,
    Widget? suffix,
  }) {
    return TextFormField(
      controller: controller,
      keyboardType: type,
      obscureText: obscure,
      validator: validator,
      style: GoogleFonts.plusJakartaSans(color: AppColors.textPrimary, fontSize: 14),
      decoration: InputDecoration(
        hintText: hint,
        hintStyle: GoogleFonts.plusJakartaSans(color: AppColors.textDim, fontSize: 14),
        prefixIcon: Icon(icon, color: AppColors.textMuted, size: 20),
        suffixIcon: suffix,
        filled: true,
        fillColor: AppColors.surfaceDeep,
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 13),
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: AppColors.border)),
        enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: AppColors.border)),
        focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: AppColors.primary, width: 2)),
        errorBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: AppColors.danger)),
        focusedErrorBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: AppColors.danger, width: 2)),
        errorStyle: GoogleFonts.plusJakartaSans(color: AppColors.danger, fontSize: 11),
      ),
    );
  }
}


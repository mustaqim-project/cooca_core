import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../core/constants/api_endpoints.dart';
import '../../core/constants/app_colors.dart';
import '../../core/network/api_client.dart';
import '../../providers/auth_provider.dart';

class TeamManagementScreen extends StatefulWidget {
  const TeamManagementScreen({super.key});

  @override
  State<TeamManagementScreen> createState() => _TeamManagementScreenState();
}

class _TeamManagementScreenState extends State<TeamManagementScreen> {
  bool _isLoading = true;
  List<Map<String, dynamic>> _members = [];
  Map<String, dynamic>? _quota;
  @override
  void initState() {
    super.initState();
    _fetchMembers();
  }

  Future<void> _fetchMembers() async {
    setState(() {
      _isLoading = true;
    });

    try {
      final res = await ApiClient.get(ApiEndpoints.members);
      if (res is Map<String, dynamic> && res['success'] == true) {
        setState(() {
          _members = (res['members'] as List?)
                  ?.map((e) => Map<String, dynamic>.from(e as Map))
                  .toList() ??
              [];
          _quota = res['quota'] != null ? Map<String, dynamic>.from(res['quota'] as Map) : null;
          _isLoading = false;
        });
      } else {
        setState(() {
          _isLoading = false;
        });
      }
    } catch (e) {
      setState(() {
        _isLoading = false;
      });
    }
  }

  void _showAddMemberModal() {
    final canAddMore = _quota?['can_add_more'] ?? true;
    final isCore = _quota?['is_core'] ?? false;

    if (!canAddMore && !isCore) {
      _showUpgradePlanModal();
      return;
    }

    final emailCtrl = TextEditingController();
    final nameCtrl = TextEditingController();
    final passCtrl = TextEditingController();
    String selectedRole = 'cashier';
    bool isSubmitting = false;

    final roles = [
      {'slug': 'admin', 'name': 'Admin Operasional', 'desc': 'Akses penuh seluruh modul bisnis kecuali billing'},
      {'slug': 'cashier', 'name': 'Kasir / Sales', 'desc': 'Operasional kasir POS (HPP/margin dirahasiakan)'},
      {'slug': 'warehouse', 'name': 'Staf Gudang', 'desc': 'Penerimaan barang dari PO & mutasi stok'},
      {'slug': 'finance', 'name': 'Staf Keuangan', 'desc': 'Faktur, beban operasional, dan laporan keuangan'},
      {'slug': 'staff', 'name': 'Staf Operasional', 'desc': 'Akses umum kasir dan inventori'},
      {'slug': 'viewer', 'name': 'Viewer (Read-Only)', 'desc': 'Hanya melihat laporan tanpa hak edit'},
    ];

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: AppColors.surface,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => StatefulBuilder(
        builder: (context, setModalState) => Padding(
          padding: EdgeInsets.only(
            left: 20,
            right: 20,
            top: 24,
            bottom: MediaQuery.of(context).viewInsets.bottom + 24,
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    'Tambah Anggota Tim',
                    style: GoogleFonts.plusJakartaSans(
                      fontSize: 18,
                      fontWeight: FontWeight.w800,
                      color: AppColors.textPrimary,
                    ),
                  ),
                  IconButton(
                    icon: const Icon(Icons.close_rounded, color: AppColors.textMuted),
                    onPressed: () => Navigator.pop(ctx),
                  ),
                ],
              ),
              const SizedBox(height: 16),
              TextField(
                controller: emailCtrl,
                keyboardType: TextInputType.emailAddress,
                style: const TextStyle(color: AppColors.textPrimary),
                decoration: InputDecoration(
                  labelText: 'Email Karyawan *',
                  labelStyle: const TextStyle(color: AppColors.textMuted),
                  hintText: 'contoh: kasir@toko.com',
                  hintStyle: const TextStyle(color: AppColors.textDim),
                  filled: true,
                  fillColor: AppColors.surfaceDeep,
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                ),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: nameCtrl,
                style: const TextStyle(color: AppColors.textPrimary),
                decoration: InputDecoration(
                  labelText: 'Nama Lengkap (Opsional)',
                  labelStyle: const TextStyle(color: AppColors.textMuted),
                  hintText: 'Nama panggilan / nama staf',
                  hintStyle: const TextStyle(color: AppColors.textDim),
                  filled: true,
                  fillColor: AppColors.surfaceDeep,
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                ),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: passCtrl,
                obscureText: true,
                style: const TextStyle(color: AppColors.textPrimary),
                decoration: InputDecoration(
                  labelText: 'Password Akun Awal (Opsional)',
                  labelStyle: const TextStyle(color: AppColors.textMuted),
                  hintText: 'Min. 6 karakter jika user baru',
                  hintStyle: const TextStyle(color: AppColors.textDim),
                  filled: true,
                  fillColor: AppColors.surfaceDeep,
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                ),
              ),
              const SizedBox(height: 16),
              Text(
                'Peran & Hak Akses (Role)',
                style: GoogleFonts.plusJakartaSans(
                  fontSize: 13,
                  fontWeight: FontWeight.w700,
                  color: AppColors.textSecondary,
                ),
              ),
              const SizedBox(height: 8),
              DropdownButtonFormField<String>(
                initialValue: selectedRole,
                dropdownColor: AppColors.surfaceDeep,
                style: const TextStyle(color: AppColors.textPrimary),
                decoration: InputDecoration(
                  filled: true,
                  fillColor: AppColors.surfaceDeep,
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                ),
                items: roles.map((r) {
                  return DropdownMenuItem<String>(
                    value: r['slug'],
                    child: Text('${r['name']}'),
                  );
                }).toList(),
                onChanged: (val) {
                  if (val != null) setModalState(() => selectedRole = val);
                },
              ),
              const SizedBox(height: 24),
              ElevatedButton(
                onPressed: isSubmitting
                    ? null
                    : () async {
                        final email = emailCtrl.text.trim();
                        if (email.isEmpty) {
                          ScaffoldMessenger.of(context).showSnackBar(
                            const SnackBar(content: Text('Email wajib diisi.')),
                          );
                          return;
                        }

                        setModalState(() => isSubmitting = true);
                        try {
                          final res = await ApiClient.post(ApiEndpoints.members, body: {
                            'email': email,
                            'name': nameCtrl.text.trim().isNotEmpty ? nameCtrl.text.trim() : null,
                            'password': passCtrl.text.trim().isNotEmpty ? passCtrl.text.trim() : null,
                            'role': selectedRole,
                          });

                          if (res is Map<String, dynamic> && res['success'] == true) {
                            Navigator.pop(ctx);
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(content: Text(res['message']?.toString() ?? 'Anggota berhasil ditambahkan.')),
                            );
                            _fetchMembers();
                          } else if (res is Map<String, dynamic> && res['error_code'] == 'RESOURCE_LIMIT_EXCEEDED') {
                            Navigator.pop(ctx);
                            _showUpgradePlanModal();
                          }
                        } catch (e) {
                          setModalState(() => isSubmitting = false);
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(content: Text('Gagal menambah anggota: $e')),
                          );
                        }
                      },
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.primary,
                  minimumSize: const Size(double.infinity, 50),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                ),
                child: isSubmitting
                    ? const SizedBox(
                        width: 20,
                        height: 20,
                        child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                      )
                    : Text(
                        'Simpan Karyawan',
                        style: GoogleFonts.plusJakartaSans(
                          fontWeight: FontWeight.w700,
                          color: Colors.black,
                        ),
                      ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  void _showUpgradePlanModal() {
    showModalBottomSheet(
      context: context,
      backgroundColor: AppColors.surface,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 56,
              height: 56,
              decoration: BoxDecoration(
                gradient: AppColors.purpleGradient,
                borderRadius: BorderRadius.circular(16),
              ),
              child: const Icon(Icons.group_add_rounded, color: Colors.white, size: 32),
            ),
            const SizedBox(height: 16),
            Text(
              'Tambah Karyawan Tanpa Batas',
              textAlign: TextAlign.center,
              style: GoogleFonts.plusJakartaSans(
                fontSize: 20,
                fontWeight: FontWeight.w800,
                color: AppColors.textPrimary,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'Paket Free Plan dibatasi 1 pengguna (Solo Owner). Tingkatkan ke Cooca Core untuk menambahkan kasir, staf gudang, dan tim tanpa batas.',
              textAlign: TextAlign.center,
              style: GoogleFonts.plusJakartaSans(
                fontSize: 13,
                color: AppColors.textMuted,
                height: 1.4,
              ),
            ),
            const SizedBox(height: 24),
            ElevatedButton(
              onPressed: () {
                Navigator.pop(ctx);
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(content: Text('Kunjungi web cooca.id atau menu Billing untuk aktivasi Core Plan.')),
                );
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.purple,
                minimumSize: const Size(double.infinity, 50),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
              ),
              child: Text(
                'Upgrade Cooca Core (Rp 129.000/bln)',
                style: GoogleFonts.plusJakartaSans(
                  fontWeight: FontWeight.w800,
                  color: Colors.white,
                ),
              ),
            ),
            const SizedBox(height: 12),
          ],
        ),
      ),
    );
  }

  void _showEditRoleModal(Map<String, dynamic> member) {
    if (member['is_owner'] == true) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Peran Owner Utama tidak dapat diubah.')),
      );
      return;
    }

    String selectedRole = member['role']?.toString() ?? 'staff';
    bool isSubmitting = false;

    final roles = [
      {'slug': 'admin', 'name': 'Admin Operasional'},
      {'slug': 'cashier', 'name': 'Kasir / Sales'},
      {'slug': 'warehouse', 'name': 'Staf Gudang'},
      {'slug': 'finance', 'name': 'Staf Keuangan'},
      {'slug': 'staff', 'name': 'Staf Operasional'},
      {'slug': 'viewer', 'name': 'Viewer (Read-Only)'},
    ];

    showModalBottomSheet(
      context: context,
      backgroundColor: AppColors.surface,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => StatefulBuilder(
        builder: (context, setModalState) => Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'Ubah Peran: ${member['name']}',
                style: GoogleFonts.plusJakartaSans(
                  fontSize: 18,
                  fontWeight: FontWeight.w800,
                  color: AppColors.textPrimary,
                ),
              ),
              const SizedBox(height: 16),
              DropdownButtonFormField<String>(
                initialValue: selectedRole,
                dropdownColor: AppColors.surfaceDeep,
                style: const TextStyle(color: AppColors.textPrimary),
                decoration: InputDecoration(
                  filled: true,
                  fillColor: AppColors.surfaceDeep,
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                ),
                items: roles.map((r) {
                  return DropdownMenuItem<String>(
                    value: r['slug'],
                    child: Text('${r['name']}'),
                  );
                }).toList(),
                onChanged: (val) {
                  if (val != null) setModalState(() => selectedRole = val);
                },
              ),
              const SizedBox(height: 24),
              ElevatedButton(
                onPressed: isSubmitting
                    ? null
                    : () async {
                        setModalState(() => isSubmitting = true);
                        try {
                          final userId = member['user_id'];
                          final res = await ApiClient.patch('${ApiEndpoints.members}/$userId', body: {
                            'role': selectedRole,
                          });
                          if (res is Map<String, dynamic> && res['success'] == true) {
                            Navigator.pop(ctx);
                            ScaffoldMessenger.of(context).showSnackBar(
                              const SnackBar(content: Text('Peran berhasil diperbarui.')),
                            );
                            _fetchMembers();
                          }
                        } catch (e) {
                          setModalState(() => isSubmitting = false);
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(content: Text('Gagal mengubah peran: $e')),
                          );
                        }
                      },
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.primary,
                  minimumSize: const Size(double.infinity, 50),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                ),
                child: isSubmitting
                    ? const SizedBox(
                        width: 20,
                        height: 20,
                        child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                      )
                    : Text(
                        'Simpan Perubahan',
                        style: GoogleFonts.plusJakartaSans(
                          fontWeight: FontWeight.w700,
                          color: Colors.black,
                        ),
                      ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  void _confirmDeleteMember(Map<String, dynamic> member) {
    if (member['is_owner'] == true) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Tidak dapat menghapus Owner Utama bisnis.')),
      );
      return;
    }

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: AppColors.surface,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Text(
          'Hapus Anggota?',
          style: GoogleFonts.plusJakartaSans(
            fontWeight: FontWeight.w800,
            color: AppColors.textPrimary,
          ),
        ),
        content: Text(
          'Apakah Anda yakin ingin menghapus ${member['name']} (${member['email']}) dari workspace bisnis ini?',
          style: const TextStyle(color: AppColors.textSecondary),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Batal', style: TextStyle(color: AppColors.textMuted)),
          ),
          ElevatedButton(
            onPressed: () async {
              Navigator.pop(ctx);
              try {
                final userId = member['user_id'];
                final res = await ApiClient.delete('${ApiEndpoints.members}/$userId');
                if (res is Map<String, dynamic> && res['success'] == true) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(content: Text('Anggota berhasil dihapus.')),
                  );
                  _fetchMembers();
                }
              } catch (e) {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(content: Text('Gagal menghapus anggota: $e')),
                );
              }
            },
            style: ElevatedButton.styleFrom(backgroundColor: AppColors.danger),
            child: const Text('Hapus', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final isOwnerOrAdmin = auth.user?.canManageTeam ?? true;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.glassNav,
        title: Text(
          'Manajemen Tim & Karyawan',
          style: GoogleFonts.plusJakartaSans(
            fontSize: 18,
            fontWeight: FontWeight.w800,
            color: AppColors.textPrimary,
          ),
        ),
        actions: [
          if (isOwnerOrAdmin)
            IconButton(
              icon: const Icon(Icons.person_add_alt_1_rounded, color: AppColors.primary),
              tooltip: 'Tambah Anggota',
              onPressed: _showAddMemberModal,
            ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
          : RefreshIndicator(
              onRefresh: _fetchMembers,
              color: AppColors.primary,
              child: ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  // Quota & Tier Status Card
                  if (_quota != null) ...[
                    Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        gradient: _quota!['is_core'] == true
                            ? AppColors.purpleGradient
                            : const LinearGradient(
                                colors: [Color(0xFF1E293B), Color(0xFF0F172A)],
                              ),
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(
                          color: _quota!['is_core'] == true ? AppColors.purpleLight.withValues(alpha: 0.3) : AppColors.borderLight,
                        ),
                      ),
                      child: Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.all(12),
                            decoration: BoxDecoration(
                              color: Colors.white.withValues(alpha: 0.15),
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: Icon(
                              _quota!['is_core'] == true ? Icons.workspace_premium_rounded : Icons.people_outline_rounded,
                              color: Colors.white,
                              size: 24,
                            ),
                          ),
                          const SizedBox(width: 14),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  'Paket: ${_quota!['plan_name']}',
                                  style: GoogleFonts.plusJakartaSans(
                                    fontSize: 14,
                                    fontWeight: FontWeight.w800,
                                    color: Colors.white,
                                  ),
                                ),
                                const SizedBox(height: 2),
                                Text(
                                  _quota!['is_core'] == true
                                      ? 'Karyawan Aktif: ${_quota!['used_users']} (Tanpa Batas)'
                                      : 'Karyawan: ${_quota!['used_users']} / 1 User (Solo Owner)',
                                  style: GoogleFonts.plusJakartaSans(
                                    fontSize: 12,
                                    color: Colors.white.withValues(alpha: 0.8),
                                  ),
                                ),
                              ],
                            ),
                          ),
                          if (_quota!['is_core'] != true)
                            ElevatedButton(
                              onPressed: _showUpgradePlanModal,
                              style: ElevatedButton.styleFrom(
                                backgroundColor: AppColors.purple,
                                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                              ),
                              child: Text(
                                'Upgrade',
                                style: GoogleFonts.plusJakartaSans(
                                  fontSize: 12,
                                  fontWeight: FontWeight.w700,
                                  color: Colors.white,
                                ),
                              ),
                            ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 16),
                  ],

                  Text(
                    'Daftar Anggota (${_members.length})',
                    style: GoogleFonts.plusJakartaSans(
                      fontSize: 14,
                      fontWeight: FontWeight.w800,
                      color: AppColors.textSecondary,
                    ),
                  ),
                  const SizedBox(height: 12),

                  if (_members.isEmpty)
                    Container(
                      padding: const EdgeInsets.all(32),
                      alignment: Alignment.center,
                      child: Column(
                        children: [
                          const Icon(Icons.people_outline_rounded, size: 48, color: AppColors.textMuted),
                          const SizedBox(height: 12),
                          Text(
                            'Belum ada anggota tim tambahan.',
                            style: GoogleFonts.plusJakartaSans(color: AppColors.textMuted),
                          ),
                        ],
                      ),
                    )
                  else
                    ..._members.map((m) {
                      final isOwner = m['is_owner'] == true;
                      final role = m['role']?.toString() ?? 'staff';
                      final roleLabel = m['role_label']?.toString() ?? role.toUpperCase();

                      Color badgeColor = AppColors.teal;
                      if (role == 'owner') badgeColor = AppColors.primary;
                      if (role == 'admin') badgeColor = AppColors.purple;
                      if (role == 'cashier') badgeColor = AppColors.cyan;
                      if (role == 'finance') badgeColor = AppColors.amber;
                      if (role == 'warehouse') badgeColor = AppColors.indigo;

                      return Container(
                        margin: const EdgeInsets.only(bottom: 12),
                        padding: const EdgeInsets.all(14),
                        decoration: BoxDecoration(
                          color: AppColors.surface,
                          borderRadius: BorderRadius.circular(14),
                          border: Border.all(color: AppColors.borderLight.withValues(alpha: 0.5)),
                        ),
                        child: Row(
                          children: [
                            CircleAvatar(
                              backgroundColor: badgeColor.withValues(alpha: 0.2),
                              child: Text(
                                (m['name']?.toString() ?? 'U').substring(0, 1).toUpperCase(),
                                style: GoogleFonts.plusJakartaSans(
                                  fontWeight: FontWeight.w800,
                                  color: badgeColor,
                                ),
                              ),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    children: [
                                      Flexible(
                                        child: Text(
                                          m['name']?.toString() ?? 'User',
                                          style: GoogleFonts.plusJakartaSans(
                                            fontSize: 14,
                                            fontWeight: FontWeight.w700,
                                            color: AppColors.textPrimary,
                                          ),
                                          overflow: TextOverflow.ellipsis,
                                        ),
                                      ),
                                      const SizedBox(width: 8),
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                        decoration: BoxDecoration(
                                          color: badgeColor.withValues(alpha: 0.15),
                                          borderRadius: BorderRadius.circular(6),
                                        ),
                                        child: Text(
                                          roleLabel,
                                          style: GoogleFonts.plusJakartaSans(
                                            fontSize: 10,
                                            fontWeight: FontWeight.w800,
                                            color: badgeColor,
                                          ),
                                        ),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 3),
                                  Text(
                                    m['email']?.toString() ?? '',
                                    style: GoogleFonts.plusJakartaSans(
                                      fontSize: 12,
                                      color: AppColors.textMuted,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                            if (isOwnerOrAdmin && !isOwner) ...[
                              IconButton(
                                icon: const Icon(Icons.edit_outlined, size: 20, color: AppColors.textSecondary),
                                tooltip: 'Ubah Role',
                                onPressed: () => _showEditRoleModal(m),
                              ),
                              IconButton(
                                icon: const Icon(Icons.delete_outline_rounded, size: 20, color: AppColors.danger),
                                tooltip: 'Hapus',
                                onPressed: () => _confirmDeleteMember(m),
                              ),
                            ],
                          ],
                        ),
                      );
                    }),
                ],
              ),
            ),
    );
  }
}


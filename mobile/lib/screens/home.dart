import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../helper/constant.dart';
import '../helper/google_auth.dart';
import '../helper/session.dart';
import '../methods/api.dart';
import '../widget/app_widgets.dart';
import 'auth/login.dart';
import 'tabs/history_tab.dart';
import 'tabs/home_tab.dart';
import 'tabs/pots_tab.dart';
import 'tabs/profile_tab.dart';
import 'tabs/scan_tab.dart';

class Home extends StatefulWidget {
  const Home({this.skipSessionCheck = false, super.key});

  final bool skipSessionCheck;

  @override
  State<Home> createState() => _HomeState();
}

class _HomeState extends State<Home> {
  int _selectedIndex = 0;
  bool _checkingSession = true;
  bool _expiryHandled = false;
  String? _sessionError;

  @override
  void initState() {
    super.initState();
    if (widget.skipSessionCheck) {
      _checkingSession = false;
    } else {
      _checkSession();
    }
  }

  Future<void> _checkSession() async {
    final user = Session.current;
    if (user == null) {
      await _expireSession();
      return;
    }

    try {
      final response = await API().getRequest(
        route: '/user',
        token: user.token,
      );
      if (response.statusCode == 401) {
        await _expireSession();
        return;
      }
      if (response.statusCode == 200) {
        _sessionError = null;
        final decoded = jsonDecode(response.body);
        if (decoded is Map && decoded['user'] is Map<String, dynamic>) {
          await Session.updateProfile(decoded['user'] as Map<String, dynamic>);
        }
      } else {
        _sessionError = responseMessage(response);
      }
    } catch (error) {
      debugPrint('Profile check failed: $error');
      _sessionError = 'Tidak dapat memuat akun. Periksa koneksi dan coba lagi.';
    } finally {
      if (mounted && !_expiryHandled) {
        setState(() => _checkingSession = false);
      }
    }
  }

  Future<void> _retrySessionCheck() async {
    setState(() {
      _checkingSession = true;
      _sessionError = null;
    });
    await _checkSession();
  }

  Future<void> _expireSession() async {
    if (_expiryHandled) return;
    _expiryHandled = true;
    try {
      await GoogleAuth.signOut();
    } catch (error) {
      debugPrint('Google sign-out failed: $error');
    }

    await Session.clear();
    if (!mounted) return;
    Navigator.of(context).pushAndRemoveUntil(
      MaterialPageRoute<void>(
        builder: (_) =>
            const Login(initialMessage: 'Sesi berakhir, silakan login lagi'),
      ),
      (_) => false,
    );
  }

  Future<void> _logout() async {
    final shouldLogout = await showDialog<bool>(
      context: context,
      builder: (dialogContext) => AlertDialog(
        title: const Text('Keluar dari akun?'),
        content: const Text('Anda yakin ingin keluar dari aplikasi?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(dialogContext, false),
            child: const Text('Batal'),
          ),
          FilledButton(
            onPressed: () => Navigator.pop(dialogContext, true),
            child: const Text('Ya, Keluar'),
          ),
        ],
      ),
    );
    if (shouldLogout != true || !mounted) return;

    final token = Session.current?.token;
    String? errorMessage;
    if (token != null) {
      try {
        final response = await API().postRequest(
          route: '/logout',
          data: const {},
          token: token,
        );
        if (response.statusCode == 401) {
          errorMessage = 'Sesi berakhir, silakan login lagi';
        } else if (response.statusCode < 200 || response.statusCode >= 300) {
          errorMessage = responseMessage(response);
        }
      } catch (error) {
        debugPrint('Logout request failed: $error');
        errorMessage =
            'Tidak dapat menghubungi server. Sesi lokal tetap dihapus.';
      }
    }

    await Session.clear();
    if (!mounted) return;
    Navigator.of(context).pushAndRemoveUntil(
      MaterialPageRoute<void>(builder: (_) => const Login()),
      (_) => false,
    );
    if (errorMessage != null) {
      WidgetsBinding.instance.addPostFrameCallback((_) {
        if (mounted) {
          ScaffoldMessenger.of(context)
              .showSnackBar(SnackBar(content: Text(errorMessage!)));
        }
      });
    }
  }

  void _selectTab(int index) {
    setState(() => _selectedIndex = index);
    SystemChrome.setSystemUIOverlayStyle(
      SystemUiOverlayStyle(
        statusBarColor: index == 1 ? scanTabColor : screenBackground,
        statusBarIconBrightness: index == 1
            ? Brightness.light
            : Brightness.dark,
        statusBarBrightness: index == 1 ? Brightness.dark : Brightness.light,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    if (_checkingSession) {
      return const Scaffold(
        body: Center(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              CircularProgressIndicator(color: primaryColor),
              SizedBox(height: space12),
              Text(
                'Memeriksa sesi...',
                style: TextStyle(color: mutedColor, fontSize: 12),
              ),
            ],
          ),
        ),
      );
    }

    if (_sessionError != null) {
      return Scaffold(
        body: SafeArea(
          child: Center(
            child: Padding(
              padding: const EdgeInsets.all(space24),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text(
                    _sessionError!,
                    textAlign: TextAlign.center,
                    style: const TextStyle(color: inkColor, fontSize: 12),
                  ),
                  const SizedBox(height: space16),
                  AppButton(label: 'Coba lagi', onPressed: _retrySessionCheck),
                ],
              ),
            ),
          ),
        ),
      );
    }

    return Scaffold(
      body: SafeArea(
        bottom: false,
        child: IndexedStack(
          index: _selectedIndex,
          children: [
            HomeTab(onSelectTab: _selectTab),
            ScanTab(active: _selectedIndex == 1),
            const PotsTab(),
            const HistoryTab(),
            ProfileTab(onLogout: _logout),
          ],
        ),
      ),
      bottomNavigationBar: _BottomNavigation(
        selectedIndex: _selectedIndex,
        onSelected: _selectTab,
      ),
    );
  }
}

class _BottomNavigation extends StatelessWidget {
  const _BottomNavigation({
    required this.selectedIndex,
    required this.onSelected,
  });

  final int selectedIndex;
  final ValueChanged<int> onSelected;

  static const _items = [
    (Icons.home_outlined, Icons.home, 'Beranda'),
    (Icons.center_focus_strong_outlined, Icons.center_focus_strong, 'Scan AI'),
    (Icons.eco_outlined, Icons.eco, 'Status Pot'),
    (Icons.history, Icons.history, 'Riwayat'),
    (Icons.person_outline, Icons.person, 'Profil'),
  ];

  @override
  Widget build(BuildContext context) {
    return Container(
      height: 76 + MediaQuery.paddingOf(context).bottom,
      padding: EdgeInsets.fromLTRB(
        space8,
        space8,
        space8,
        space8 + MediaQuery.paddingOf(context).bottom,
      ),
      decoration: const BoxDecoration(
        color: Colors.white,
        border: Border(top: BorderSide(color: borderColor)),
      ),
      child: Row(
        children: List.generate(_items.length, (index) {
          final item = _items[index];
          final selected = selectedIndex == index;
          if (index == 1) {
            return Expanded(
              child: InkWell(
                key: const ValueKey('tab-scan'),
                onTap: () => onSelected(index),
                borderRadius: BorderRadius.circular(controlRadius),
                child: Column(
                  children: [
                    Container(
                      width: 39,
                      height: 36,
                      decoration: BoxDecoration(
                        color: selected ? primaryColor : scanTabColor,
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Icon(
                        selected ? item.$2 : item.$1,
                        color: Colors.white,
                        size: 20,
                      ),
                    ),
                    const SizedBox(height: 3),
                    Text(
                      item.$3,
                      style: TextStyle(
                        color: selected ? primaryColor : mutedColor,
                        fontSize: 11,
                        fontWeight: selected
                            ? FontWeight.w800
                            : FontWeight.w600,
                      ),
                    ),
                  ],
                ),
              ),
            );
          }
          return Expanded(
            child: InkWell(
              key: ValueKey(
                'tab-${item.$3.toLowerCase().replaceAll(' ', '-')}',
              ),
              onTap: () => onSelected(index),
              child: SizedBox(
                height: 48,
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(
                      selected ? item.$2 : item.$1,
                      color: selected ? primaryColor : subtleColor,
                      size: 22,
                    ),
                    const SizedBox(height: space4),
                    Text(
                      item.$3,
                      maxLines: 1,
                      style: TextStyle(
                        color: selected ? primaryColor : mutedColor,
                        fontSize: 11,
                        fontWeight: selected
                            ? FontWeight.w800
                            : FontWeight.w600,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          );
        }),
      ),
    );
  }
}

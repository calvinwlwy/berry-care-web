import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

import '../helper/constant.dart';
import '../methods/api.dart';
import 'auth/login.dart';

class Home extends StatefulWidget {
  const Home({super.key});

  @override
  State<Home> createState() => _HomeState();
}

class _HomeState extends State<Home> {
  late SharedPreferences preferences;
  final TextEditingController nameController = TextEditingController();
  final TextEditingController emailController = TextEditingController();
  final TextEditingController passwordController = TextEditingController();
  final API _api = API();

  bool isLoading = true;
  bool _isSaving = false;
  bool _handlingSessionExpiry = false;
  bool _preferencesReady = false;
  StateSetter? _dialogStateSetter;
  int _selectedIndex = 0;
  String _role = '';
  List<Map<String, dynamic>> users = [];

  @override
  void initState() {
    super.initState();
    _initialize();
  }

  Future<void> _initialize() async {
    await getUserData();
    if (!mounted || !_preferencesReady || _handlingSessionExpiry) return;

    if (preferences.getString('token') != null && _role == 'admin') {
      await getAllUsers();
    } else if (mounted) {
      setState(() => isLoading = false);
    }
  }

  Future<void> getUserData() async {
    setState(() => isLoading = true);

    try {
      preferences = await SharedPreferences.getInstance();
      _preferencesReady = true;
      final token = preferences.getString('token');
      if (token == null) {
        await _expireSession();
        return;
      }

      final result = await _api.getRequest(route: '/user', token: token);
      if (result.statusCode == 401) {
        await _expireSession();
        return;
      }

      if (result.statusCode == 200) {
        final response = jsonDecode(result.body) as Map<String, dynamic>;
        final profile = response['user'] as Map<String, dynamic>;
        _role = profile['role'] as String? ?? '';
        await preferences.setString('role', _role);
        if (profile['id'] is int) {
          await preferences.setInt('user_id', profile['id'] as int);
        }
        if (profile['name'] is String) {
          await preferences.setString('name', profile['name'] as String);
        }
        if (profile['email'] is String) {
          await preferences.setString('email', profile['email'] as String);
        }
      } else if (mounted) {
        _showMessage(_messageFromResponse(result));
      }
    } catch (e) {
      debugPrint('Get profile error: $e');
      if (mounted) _showMessage('Tidak dapat terhubung ke server: $e');
    } finally {
      if (mounted && !_handlingSessionExpiry) {
        setState(() => isLoading = false);
      }
    }
  }

  Future<void> getAllUsers() async {
    if (!mounted) return;

    if (_role != 'admin') {
      setState(() {
        users = [];
        isLoading = false;
      });
      return;
    }

    setState(() => isLoading = true);
    try {
      final token = preferences.getString('token');
      final result = await _api.getRequest(route: '/getuser', token: token);

      if (result.statusCode == 401) {
        await _expireSession();
        return;
      }
      if (result.statusCode == 403) {
        setState(() {
          _role = '';
          users = [];
        });
        _showMessage('Hanya admin yang dapat mengelola pengguna');
        return;
      }

      if (result.statusCode == 200) {
        final response = jsonDecode(result.body) as Map<String, dynamic>;
        final data = response['data'] as List<dynamic>;
        setState(() {
          users = data.whereType<Map<String, dynamic>>().toList(
            growable: false,
          );
        });
      } else {
        _showMessage(_messageFromResponse(result));
      }
    } catch (e) {
      debugPrint('Get users error: $e');
      if (mounted) _showMessage('Tidak dapat memuat pengguna: $e');
    } finally {
      if (mounted && !_handlingSessionExpiry) {
        setState(() => isLoading = false);
      }
    }
  }

  Future<void> _expireSession() async {
    if (_handlingSessionExpiry) return;
    _handlingSessionExpiry = true;
    final messenger = ScaffoldMessenger.maybeOf(context);
    await preferences.clear();
    if (!mounted) return;

    messenger?.showSnackBar(
      const SnackBar(content: Text('Sesi berakhir, silakan login lagi')),
    );
    Navigator.pushReplacement(
      context,
      MaterialPageRoute<void>(builder: (_) => const Login()),
    );
  }

  String _messageFromResponse(http.Response result) {
    final decoded = jsonDecode(result.body);
    if (result.statusCode == 422 &&
        decoded is Map &&
        decoded['errors'] is Map) {
      final errors = decoded['errors'] as Map;
      for (final value in errors.values) {
        if (value is List && value.isNotEmpty) {
          return value.first.toString();
        }
      }
    }

    if (decoded is Map && decoded['message'] is String) {
      return decoded['message'] as String;
    }
    return 'Terjadi kesalahan. Silakan coba lagi.';
  }

  void _showMessage(String message) {
    if (!mounted) return;
    ScaffoldMessenger.of(context)
      ..hideCurrentSnackBar()
      ..showSnackBar(SnackBar(content: Text(message)));
  }

  Future<void> logout() async {
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
          ElevatedButton(
            onPressed: () => Navigator.pop(dialogContext, true),
            child: const Text('Ya, Keluar'),
          ),
        ],
      ),
    );
    if (shouldLogout != true || !mounted) return;

    final token = preferences.getString('token');
    if (token != null) {
      try {
        final response = await _api.postRequest(
          route: '/logout',
          data: const {},
          token: token,
        );
        if (response.statusCode == 401) {
          await _expireSession();
          return;
        }
      } catch (e) {
        debugPrint('Logout request failed: $e');
      }
    }

    await preferences.clear();
    if (!mounted) return;
    Navigator.pushReplacement(
      context,
      MaterialPageRoute<void>(builder: (_) => const Login()),
    );
  }

  Future<void> showUserDialog({Map<String, dynamic>? user}) async {
    nameController.text = user?['name'] as String? ?? '';
    emailController.text = user?['email'] as String? ?? '';
    passwordController.clear();
    _isSaving = false;

    await showDialog<void>(
      context: context,
      builder: (dialogContext) => StatefulBuilder(
        builder: (dialogContext, setDialogState) {
          _dialogStateSetter = setDialogState;
          return AlertDialog(
            title: Text(user == null ? 'Add User' : 'Edit User'),
            content: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  TextField(
                    controller: nameController,
                    decoration: const InputDecoration(labelText: 'Name'),
                    textCapitalization: TextCapitalization.words,
                  ),
                  TextField(
                    controller: emailController,
                    decoration: const InputDecoration(labelText: 'Email'),
                    keyboardType: TextInputType.emailAddress,
                  ),
                  TextField(
                    controller: passwordController,
                    decoration: const InputDecoration(labelText: 'Password'),
                    obscureText: true,
                  ),
                ],
              ),
            ),
            actions: [
              TextButton(
                onPressed: _isSaving
                    ? null
                    : () {
                        _dialogStateSetter = null;
                        Navigator.pop(dialogContext);
                      },
                child: const Text('Cancel'),
              ),
              ElevatedButton(
                style: ElevatedButton.styleFrom(
                  backgroundColor: primaryColor,
                  foregroundColor: Colors.white,
                ),
                onPressed: _isSaving
                    ? null
                    : () => saveUser(id: user?['id'] as int?),
                child: _isSaving
                    ? const SizedBox(
                        width: 18,
                        height: 18,
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          color: Colors.white,
                        ),
                      )
                    : const Text('Save'),
              ),
            ],
          );
        },
      ),
    );
    _dialogStateSetter = null;
  }

  Future<void> saveUser({int? id}) async {
    if (_isSaving) return;
    setState(() => _isSaving = true);
    _dialogStateSetter?.call(() => _isSaving = true);

    final data = <String, dynamic>{
      'name': nameController.text.trim(),
      'email': emailController.text.trim(),
    };
    if (id == null || passwordController.text.isNotEmpty) {
      data['password'] = passwordController.text;
    }

    try {
      final token = preferences.getString('token');
      final result = id == null
          ? await _api.postRequest(route: '/adduser', data: data, token: token)
          : await _api.putRequest(
              route: '/updateuser/$id',
              data: data,
              token: token,
            );

      if (result.statusCode == 401) {
        if (!mounted) return;
        _dialogStateSetter = null;
        Navigator.pop(context);
        await _expireSession();
        return;
      }
      if (result.statusCode == 403) {
        _showMessage('Hanya admin yang dapat mengelola pengguna');
        return;
      }
      if (result.statusCode != 200 && result.statusCode != 201) {
        _showMessage(_messageFromResponse(result));
        return;
      }

      final response = jsonDecode(result.body) as Map<String, dynamic>;
      final message = response['message'] as String;
      if (!mounted) return;
      _dialogStateSetter = null;
      Navigator.pop(context);
      await getAllUsers();
      if (mounted) _showMessage(message);
    } catch (e) {
      debugPrint('Save user error: $e');
      if (mounted) _showMessage('Tidak dapat menyimpan pengguna: $e');
    } finally {
      if (mounted) {
        setState(() => _isSaving = false);
        _dialogStateSetter?.call(() => _isSaving = false);
      }
    }
  }

  Future<void> deleteUser(int id) async {
    final currentUserId = preferences.getInt('user_id');
    if (id == currentUserId) return;

    final shouldDelete = await showDialog<bool>(
      context: context,
      builder: (dialogContext) => AlertDialog(
        title: const Text('Hapus pengguna?'),
        content: const Text('Data pengguna ini akan dihapus.'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(dialogContext, false),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.red,
              foregroundColor: Colors.white,
            ),
            onPressed: () => Navigator.pop(dialogContext, true),
            child: const Text('Ya, Hapus'),
          ),
        ],
      ),
    );
    if (shouldDelete != true || !mounted) return;

    try {
      final result = await _api.deleteRequest(
        route: '/deleteuser/$id',
        token: preferences.getString('token'),
      );
      if (result.statusCode == 401) {
        await _expireSession();
        return;
      }
      if (result.statusCode == 403) {
        _showMessage('Hanya admin yang dapat mengelola pengguna');
        return;
      }
      if (result.statusCode != 200) {
        _showMessage(_messageFromResponse(result));
        return;
      }

      final response = jsonDecode(result.body) as Map<String, dynamic>;
      await getAllUsers();
      if (mounted) _showMessage(response['message'] as String);
    } catch (e) {
      debugPrint('Delete user error: $e');
      if (mounted) _showMessage('Tidak dapat menghapus pengguna: $e');
    }
  }

  Widget buildHomePage() {
    final token = preferences.getString('token') ?? '';
    final previewToken = token.length > 8
        ? '${token.substring(0, 8)}...'
        : token;

    return Container(
      decoration: BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topRight,
          end: Alignment.bottomLeft,
          colors: [primaryColor, secondaryColor],
        ),
      ),
      child: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(20),
          child: isLoading
              ? const Center(
                  child: CircularProgressIndicator(color: Colors.white),
                )
              : Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Text(
                      'Welcome Back!',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 28,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 10),
                    _homeDetail('ID User', '${preferences.getInt('user_id')}'),
                    _homeDetail('Name', preferences.getString('name') ?? ''),
                    _homeDetail('Email', preferences.getString('email') ?? ''),
                    _homeDetail('Role', _role),
                    _homeDetail('Token', previewToken),
                    const SizedBox(height: 30),
                    Center(
                      child: ElevatedButton(
                        onPressed: logout,
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.white,
                          foregroundColor: primaryColor,
                          padding: const EdgeInsets.symmetric(
                            horizontal: 40,
                            vertical: 15,
                          ),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(30),
                          ),
                        ),
                        child: const Text('Logout'),
                      ),
                    ),
                  ],
                ),
        ),
      ),
    );
  }

  Widget _homeDetail(String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(top: 5),
      child: Text(
        '$label: $value',
        style: const TextStyle(color: Colors.white, fontSize: 18),
      ),
    );
  }

  Widget buildCrudPage() {
    return Container(
      decoration: BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topRight,
          end: Alignment.bottomLeft,
          colors: [primaryColor, secondaryColor],
        ),
      ),
      child: SafeArea(
        child: Column(
          children: [
            Padding(
              padding: const EdgeInsets.all(20),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text(
                    'Manage Users',
                    style: TextStyle(
                      color: Colors.white,
                      fontSize: 24,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  if (_role == 'admin')
                    FloatingActionButton(
                      backgroundColor: Colors.white,
                      foregroundColor: primaryColor,
                      onPressed: () => showUserDialog(),
                      child: const Icon(Icons.add),
                    ),
                ],
              ),
            ),
            Expanded(
              child: isLoading
                  ? const Center(
                      child: CircularProgressIndicator(color: Colors.white),
                    )
                  : _role != 'admin'
                  ? const Center(
                      child: Padding(
                        padding: EdgeInsets.all(24),
                        child: Text(
                          'Hanya admin yang dapat mengelola pengguna',
                          textAlign: TextAlign.center,
                          style: TextStyle(color: Colors.white, fontSize: 18),
                        ),
                      ),
                    )
                  : ListView.builder(
                      itemCount: users.length,
                      itemBuilder: (context, index) {
                        final user = users[index];
                        final id = user['id'] as int;
                        final isCurrentUser =
                            id == preferences.getInt('user_id');

                        return Card(
                          color: Colors.white.withValues(alpha: 0.9),
                          margin: const EdgeInsets.symmetric(
                            horizontal: 15,
                            vertical: 8,
                          ),
                          child: ListTile(
                            title: Text(user['name'] as String? ?? ''),
                            subtitle: Text(user['email'] as String? ?? ''),
                            trailing: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                IconButton(
                                  onPressed: () => showUserDialog(user: user),
                                  icon: const Icon(
                                    Icons.edit,
                                    color: Colors.blueAccent,
                                  ),
                                ),
                                IconButton(
                                  onPressed: isCurrentUser
                                      ? null
                                      : () => deleteUser(id),
                                  icon: const Icon(
                                    Icons.delete,
                                    color: Colors.red,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        );
                      },
                    ),
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: _selectedIndex == 0 ? buildHomePage() : buildCrudPage(),
      bottomNavigationBar: BottomNavigationBar(
        backgroundColor: Colors.white,
        selectedItemColor: primaryColor,
        unselectedItemColor: Colors.grey,
        currentIndex: _selectedIndex,
        onTap: (index) {
          setState(() => _selectedIndex = index);
          if (index == 1 && _role == 'admin') {
            getAllUsers();
          }
        },
        items: const [
          BottomNavigationBarItem(icon: Icon(Icons.home), label: 'Home'),
          BottomNavigationBarItem(icon: Icon(Icons.people), label: 'Users'),
        ],
      ),
    );
  }

  @override
  void dispose() {
    nameController.dispose();
    emailController.dispose();
    passwordController.dispose();
    super.dispose();
  }
}

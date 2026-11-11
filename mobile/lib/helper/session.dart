import 'package:shared_preferences/shared_preferences.dart';

class SessionUser {
  const SessionUser({
    required this.id,
    required this.name,
    required this.email,
    required this.role,
    required this.token,
  });

  final int id;
  final String name;
  final String email;
  final String role;
  final String token;

  factory SessionUser.fromApi(Map<String, dynamic> user, String token) {
    return SessionUser(
      id: user['id'] as int,
      name: user['name'] as String? ?? '',
      email: user['email'] as String? ?? '',
      role: user['role'] as String? ?? 'petani',
      token: token,
    );
  }

  SessionUser copyWith({
    int? id,
    String? name,
    String? email,
    String? role,
    String? token,
  }) {
    return SessionUser(
      id: id ?? this.id,
      name: name ?? this.name,
      email: email ?? this.email,
      role: role ?? this.role,
      token: token ?? this.token,
    );
  }
}

class Session {
  Session._();

  static const _tokenKey = 'token';
  static const _idKey = 'user_id';
  static const _nameKey = 'name';
  static const _emailKey = 'email';
  static const _roleKey = 'role';

  static SessionUser? _current;
  static bool _remembered = false;

  static SessionUser? get current => _current;

  static Future<SessionUser?> restore() async {
    final preferences = await SharedPreferences.getInstance();
    final token = preferences.getString(_tokenKey);
    if (token == null || token.isEmpty) {
      _current = null;
      _remembered = false;
      return null;
    }

    _remembered = true;
    _current = SessionUser(
      id: preferences.getInt(_idKey) ?? 0,
      name: preferences.getString(_nameKey) ?? '',
      email: preferences.getString(_emailKey) ?? '',
      role: preferences.getString(_roleKey) ?? 'petani',
      token: token,
    );
    return _current;
  }

  static Future<void> start(SessionUser user, {required bool remember}) async {
    _current = user;
    _remembered = remember;
    final preferences = await SharedPreferences.getInstance();
    await _removeStoredSession(preferences);

    if (!remember) return;

    await preferences.setString(_tokenKey, user.token);
    await preferences.setInt(_idKey, user.id);
    await preferences.setString(_nameKey, user.name);
    await preferences.setString(_emailKey, user.email);
    await preferences.setString(_roleKey, user.role);
  }

  static Future<void> updateProfile(Map<String, dynamic> profile) async {
    final user = _current;
    if (user == null) return;

    _current = user.copyWith(
      id: profile['id'] is int ? profile['id'] as int : null,
      name: profile['name'] is String ? profile['name'] as String : null,
      email: profile['email'] is String ? profile['email'] as String : null,
      role: profile['role'] is String ? profile['role'] as String : null,
    );

    if (!_remembered) return;

    final preferences = await SharedPreferences.getInstance();
    await preferences.setInt(_idKey, _current!.id);
    await preferences.setString(_nameKey, _current!.name);
    await preferences.setString(_emailKey, _current!.email);
    await preferences.setString(_roleKey, _current!.role);
  }

  static Future<void> clear() async {
    _current = null;
    _remembered = false;
    final preferences = await SharedPreferences.getInstance();
    await _removeStoredSession(preferences);
  }

  static Future<void> _removeStoredSession(
    SharedPreferences preferences,
  ) async {
    await preferences.remove(_tokenKey);
    await preferences.remove(_idKey);
    await preferences.remove(_nameKey);
    await preferences.remove(_emailKey);
    await preferences.remove(_roleKey);
  }
}

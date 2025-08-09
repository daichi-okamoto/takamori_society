import 'package:flutter/material.dart';
import '../core/api_client.dart';
import '../features/auth/auth_service.dart';

class LoginPage extends StatefulWidget {
  const LoginPage({super.key});
  @override
  State<LoginPage> createState() => _LoginPageState();
}

class _LoginPageState extends State<LoginPage> {
  final _email = TextEditingController();
  final _pass  = TextEditingController();
  final _formKey = GlobalKey<FormState>();
  bool _loading = false;
  String? _error;

  late final AuthService _auth;

  @override
  void initState() {
    super.initState();
    _auth = AuthService(ApiClient());
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() { _loading = true; _error = null; });
    try {
      await _auth.login(email: _email.text.trim(), password: _pass.text);
      if (!mounted) return;
      Navigator.pushReplacementNamed(context, '/home');
    } on DioException catch (e) {
      setState(() => _error = e.response?.data?['message']?.toString() ?? '通信エラー');
    } catch (e) {
      setState(() => _error = 'ログインに失敗しました');
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('ログイン')),
      body: Padding(
        padding: const EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(
            children: [
              TextFormField(
                controller: _email,
                decoration: const InputDecoration(labelText: 'メールアドレス'),
                keyboardType: TextInputType.emailAddress,
                validator: (v) => (v == null || v.isEmpty) ? '必須です' : null,
              ),
              const SizedBox(height: 8),
              TextFormField(
                controller: _pass,
                decoration: const InputDecoration(labelText: 'パスワード'),
                obscureText: true,
                validator: (v) => (v == null || v.length < 8) ? '8文字以上' : null,
              ),
              const SizedBox(height: 16),
              if (_error != null)
                Text(_error!, style: const TextStyle(color: Colors.red)),
              const SizedBox(height: 8),
              FilledButton(
                onPressed: _loading ? null : _submit,
                child: _loading
                    ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2))
                    : const Text('ログイン'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

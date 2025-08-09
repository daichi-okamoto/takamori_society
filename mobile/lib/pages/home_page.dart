import 'package:flutter/material.dart';
import '../core/api_client.dart';
import '../features/auth/auth_service.dart';

class HomePage extends StatefulWidget {
  const HomePage({super.key});
  @override
  State<HomePage> createState() => _HomePageState();
}

class _HomePageState extends State<HomePage> {
  final _api = ApiClient();
  late final AuthService _auth = AuthService(_api);
  String _result = '未取得';

  Future<void> _fetchTournaments() async {
    try {
      final res = await _api.dio.get('/tournaments'); // ★Laravel側のAPIに合わせてね
      setState(() => _result = res.data.toString());
    } catch (e) {
      setState(() => _result = 'エラー: $e');
    }
  }

  Future<void> _logout() async {
    await _auth.logout();
    if (!mounted) return;
    Navigator.pushReplacementNamed(context, '/login');
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('ホーム'), actions: [
        IconButton(onPressed: _logout, icon: const Icon(Icons.logout)),
      ]),
      body: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            FilledButton(onPressed: _fetchTournaments, child: const Text('大会一覧を取得')),
            const SizedBox(height: 12),
            Expanded(child: SingleChildScrollView(child: Text(_result))),
          ],
        ),
      ),
    );
  }
}

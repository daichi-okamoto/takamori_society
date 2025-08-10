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

  // TODO: 初回マウントでAPI叩いて埋める
  String? _tournamentName = '高森ソサイチ 2025 秋';
  String? _tournamentDate = '2025/09/01';

  Future<void> _logout() async {
    await _auth.logout();
    if (!mounted) return;
    Navigator.pushReplacementNamed(context, '/login');
  }

  Future<void> _reload() async {
    // TODO: /tournaments /notifications /galleries を取得してsetState
    await Future.delayed(const Duration(milliseconds: 500));
    setState(() {});
  }

  @override
  Widget build(BuildContext context) {
    final cs = Theme.of(context).colorScheme;

    return Scaffold(
      appBar: AppBar(
        title: const Text('ホーム'),
        actions: [IconButton(onPressed: _logout, icon: const Icon(Icons.logout))],
      ),
      body: RefreshIndicator(
        onRefresh: _reload,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            // 1) ヘッダー
            Card(
              color: cs.primaryContainer,
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Row(
                  children: [
                    Icon(Icons.emoji_events, color: cs.onPrimaryContainer, size: 28),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(_tournamentName ?? '大会未選択',
                              style: TextStyle(
                                color: cs.onPrimaryContainer,
                                fontSize: 18, fontWeight: FontWeight.bold)),
                          const SizedBox(height: 4),
                          Text(_tournamentDate ?? '',
                              style: TextStyle(color: cs.onPrimaryContainer.withOpacity(0.9))),
                        ],
                      ),
                    ),
                    FilledButton.tonal(
                      onPressed: () {
                        // TODO: 大会選択画面へ遷移
                      },
                      child: const Text('変更'),
                    ),
                  ],
                ),
              ),
            ),

            const SizedBox(height: 12),

            // 2) クイックカード（グリッド）
            GridView.count(
              physics: const NeverScrollableScrollPhysics(),
              crossAxisCount: 2,
              shrinkWrap: true,
              crossAxisSpacing: 12,
              mainAxisSpacing: 12,
              childAspectRatio: 1.5,
              children: [
                _DashCard(
                  icon: Icons.emoji_events, label: '大会',
                  onTap: () { /* TODO: 大会一覧へ */ },
                ),
                _DashCard(
                  icon: Icons.sports_soccer, label: '試合',
                  onTap: () { /* TODO: 試合一覧へ（予定/結果タブ） */ },
                ),
                _DashCard(
                  icon: Icons.notifications, label: '通知',
                  onTap: () { /* TODO: 通知一覧へ */ },
                ),
                _DashCard(
                  icon: Icons.photo_library, label: 'ギャラリー',
                  onTap: () { /* TODO: ギャラリーへ */ },
                ),
                _DashCard(
                  icon: Icons.groups, label: '所属チーム',
                  onTap: () { /* TODO: マイチーム詳細へ（roleで出し分け） */ },
                ),
                _DashCard(
                  icon: Icons.person, label: 'プロフィール',
                  onTap: () => Navigator.pushNamed(context, '/profile'),
                ),
              ],
            ),

            const SizedBox(height: 16),

            // 3) 直近の試合
            _SectionHeader(
              title: '直近の試合',
              onSeeAll: () { /* TODO: 試合一覧へ */ },
            ),
            const SizedBox(height: 8),
            const _GameList(items: [
              GameItem(title: 'A組 第3試合', a: 'Takamori FC', b: 'Azalee', time: '09:30'),
              GameItem(title: 'A組 第4試合', a: 'Blue Stars', b: 'Cerezo', time: '10:10'),
            ]),

            const SizedBox(height: 16),

            // 4) お知らせ
            _SectionHeader(
              title: 'お知らせ',
              onSeeAll: () { /* TODO: 通知一覧へ */ },
            ),
            const SizedBox(height: 8),
            const _AnnouncementList(items: [
              ('集合時間変更', '受付は12:40に前倒しします'),
              ('駐車場の案内', '第2駐車場をご利用ください'),
            ]),

            const SizedBox(height: 16),

            // 5) 最近のギャラリー（横スクロール）
            _SectionHeader(
              title: 'ギャラリー',
              onSeeAll: () { /* TODO: ギャラリー一覧へ */ },
            ),
            const SizedBox(height: 8),
            const _GalleryRow(imageUrls: [
              'https://picsum.photos/seed/1/300/200',
              'https://picsum.photos/seed/2/300/200',
              'https://picsum.photos/seed/3/300/200',
              'https://picsum.photos/seed/4/300/200',
            ]),
          ],
        ),
      ),
    );
  }
}

// ---- UI部品 ----

class _DashCard extends StatelessWidget {
  const _DashCard({required this.icon, required this.label, required this.onTap});
  final IconData icon;
  final String label;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final cs = Theme.of(context).colorScheme;
    return Card(
      child: InkWell(
        onTap: onTap,
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(icon, size: 32, color: cs.primary),
              const SizedBox(height: 8),
              Text(label, style: const TextStyle(fontWeight: FontWeight.w600)),
            ],
          ),
        ),
      ),
    );
  }
}

class _SectionHeader extends StatelessWidget {
  const _SectionHeader({required this.title, this.onSeeAll});
  final String title;
  final VoidCallback? onSeeAll;

  @override
  Widget build(BuildContext context) {
    final cs = Theme.of(context).colorScheme;
    return Row(
      children: [
        Text(title, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
        const Spacer(),
        if (onSeeAll != null)
          TextButton.icon(
            onPressed: onSeeAll,
            label: const Text('すべて見る'),
            icon: Icon(Icons.chevron_right, color: cs.primary),
          ),
      ],
    );
  }
}

class GameItem {
  final String title, a, b, time;
  const GameItem({required this.title, required this.a, required this.b, required this.time});
}

class _GameList extends StatelessWidget {
  const _GameList({required this.items});
  final List<GameItem> items;
  @override
  Widget build(BuildContext context) {
    final cs = Theme.of(context).colorScheme;
    return Column(
      children: items.map((g) => Card(
        child: ListTile(
          leading: CircleAvatar(
            backgroundColor: cs.primaryContainer,
            child: Icon(Icons.sports_soccer, color: cs.onPrimaryContainer),
          ),
          title: Text(g.title, maxLines: 1, overflow: TextOverflow.ellipsis),
          subtitle: Text('${g.a}  vs  ${g.b}'),
          trailing: Text(g.time, style: const TextStyle(fontWeight: FontWeight.bold)),
          onTap: () { /* TODO: 試合詳細へ */ },
        ),
      )).toList(),
    );
  }
}

class _AnnouncementList extends StatelessWidget {
  const _AnnouncementList({required this.items});
  final List<(String, String)> items;
  @override
  Widget build(BuildContext context) {
    return Column(
      children: items.map((e) => Card(
        child: ListTile(
          leading: const Icon(Icons.campaign),
          title: Text(e.$1, maxLines: 1, overflow: TextOverflow.ellipsis),
          subtitle: Text(e.$2),
        ),
      )).toList(),
    );
  }
}

class _GalleryRow extends StatelessWidget {
  const _GalleryRow({required this.imageUrls});
  final List<String> imageUrls;
  @override
  Widget build(BuildContext context) {
    return SizedBox(
      height: 110,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        itemCount: imageUrls.length,
        separatorBuilder: (_, __) => const SizedBox(width: 8),
        itemBuilder: (_, i) => AspectRatio(
          aspectRatio: 16/9,
          child: ClipRRect(
            borderRadius: BorderRadius.circular(12),
            child: Image.network(imageUrls[i], fit: BoxFit.cover),
          ),
        ),
      ),
    );
  }
}

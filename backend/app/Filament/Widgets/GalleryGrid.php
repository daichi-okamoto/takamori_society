<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\GalleryResource;
use App\Models\Gallery;
use App\Models\Tournament;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;

class GalleryGrid extends Widget
{
    protected static string $view = 'filament.widgets.gallery-grid';
    protected int|string|array $columnSpan = 'full';
    protected static bool $isLazy = false;

    // ====== フィルタ ======
    public ?int $tournamentFilter = null; // 選択中の大会ID

    // ====== モーダル表示用データ ======
    public ?string $lightboxSrc = null;
    public ?string $lightboxAlt = null;

    // ====== ナビ用の状態 ======
    /** @var array<int> 並び順のID配列（最新順） */
    public array $lightboxIds = [];
    /** 現在位置（$lightboxIds のインデックス） */
    public int $lightboxIndex = 0;

    // 一覧クエリ：必要列をちゃんと取得
    protected function galleriesQuery(): Builder
    {
        return Gallery::query()
            ->when($this->tournamentFilter, fn (Builder $q) => $q->where('tournament_id', $this->tournamentFilter))
            ->latest('created_at');
    }

    // クリックで画像をID指定で開く（フィルタ反映）
    public function openLightboxById(int $galleryId): void
    {
        // 表示件数と同じ並びでID配列を作る
        $this->lightboxIds = $this->galleriesQuery()
            ->limit(48)
            ->pluck('id')
            ->all();

        // 現在のインデックスを特定
        $index = array_search($galleryId, $this->lightboxIds, true);
        if ($index === false) {
            $index = 0; // フォールバック
        }
        $this->setCurrentByIndex($index);

        // Filamentモーダルを開く（イベント）
        $this->dispatch('open-modal', id: 'galleryLightbox');
    }

    // 前へ
    public function prevImage(): void
    {
        if (empty($this->lightboxIds)) {
            return;
        }

        $i = $this->lightboxIndex - 1;
        if ($i < 0) {
            $i = count($this->lightboxIds) - 1; // ループ
        }
        $this->setCurrentByIndex($i);
    }

    // 次へ
    public function nextImage(): void
    {
        if (empty($this->lightboxIds)) {
            return;
        }

        $i = $this->lightboxIndex + 1;
        if ($i >= count($this->lightboxIds)) {
            $i = 0; // ループ
        }
        $this->setCurrentByIndex($i);
    }

    // 共通：index に応じて lightboxSrc/Alt を差し替える
    protected function setCurrentByIndex(int $index): void
    {
        $this->lightboxIndex = $index;

        $id = $this->lightboxIds[$index] ?? null;
        if (!$id) return;

        $g = Gallery::find($id);
        if (!$g) return;

        // R2対応：モデルのアクセサURLを使う
        $this->lightboxSrc = $g->url;           // ← ここが重要
        $this->lightboxAlt = 'gallery-' . $g->id;
    }

    // テンプレートへ渡すデータ（フィルタ反映）
    protected function getViewData(): array
    {
        return [
            'galleries' => $this->galleriesQuery()
                ->limit(48)
                // → 取得列絞り込みをやめる or 必要列を含める
                // ->get(['id', 'tournament_id', 'uploaded_by', 'created_at', 'disk', 'path', 'visibility'])
                ->get(),
            'tournaments' => \App\Models\Tournament::orderBy('name')->pluck('name', 'id'),
        ];
    }

    // 現在のIDを取得
    public function getCurrentId(): ?int
    {
        return $this->lightboxIds[$this->lightboxIndex] ?? null;
    }

    // 編集へ（モーダルを閉じてからリダイレクト）
    public function goToEdit(): void
    {
        if (!$id = $this->getCurrentId()) {
            return;
        }

        $this->dispatch('close-modal', id: 'galleryLightbox');
        $this->redirect(GalleryResource::getUrl('edit', ['record' => $id]));
    }

    // 削除（ソフトデリート想定）
    public function deleteCurrentImage(): void
    {
        if (!$id = $this->getCurrentId()) {
            return;
        }

        $g = Gallery::find($id);
        if (!$g) {
            return;
        }

        $g->delete();

        // 次の画像へ進む or 画像が無ければ閉じる
        if (count($this->lightboxIds) > 1) {
            // 今のIDを配列から除外
            $this->lightboxIds = array_values(array_filter(
                $this->lightboxIds,
                fn ($x) => $x !== $id
            ));

            if ($this->lightboxIndex >= count($this->lightboxIds)) {
                $this->lightboxIndex = 0;
            }

            $this->setCurrentByIndex($this->lightboxIndex);
        } else {
            $this->dispatch('close-modal', id: 'galleryLightbox');
        }

        Notification::make()
            ->title('画像を削除しました')
            ->success()
            ->send();
    }

    public static function canView(): bool
    {
        // ギャラリーのリスト/作成/編集系でのみ表示（お好みで調整）
        return request()->routeIs([
            'filament.admin.resources.galleries.index',
            'filament.admin.resources.galleries.create',
            'filament.admin.resources.galleries.edit',
        ]);
    }
}

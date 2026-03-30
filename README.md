# DDUI — Data-Driven UI for PocketMine-MP

PocketMine-MP 向けのデータドリブン UI ライブラリです。
Minecraft Bedrock Edition のカスタムフォームを、リアクティブな値バインディングで構築できます。

移植元: [PowerNukkitX](https://github.com/PowerNukkitX/PowerNukkitX/tree/master/src/main/java/cn/nukkit/ddui)

## 動作要件

- PHP 8.1+
- PocketMine-MP API 5.0.0

## インストール

`plugin.yml` の依存関係に追加するか、プラグインフォルダに配置してください。

```yaml
name: MyPlugin
depend: [DDUI]
```

---

## 基本的な使い方

### フォームの作成と表示

```php
use soradore\DDUI\CustomForm;
use soradore\DDUI\Observable;

$notifications = new Observable(true);
$volume        = new Observable(80.0);
$name          = new Observable('');

$form = (new CustomForm('設定'))
    ->header('通知')
    ->toggle('通知を受け取る', $notifications,
        onChange: fn(Player $p, bool $v) => saveNotification($p, $v)
    )
    ->spacer()
    ->header('音声')
    ->slider('BGM 音量', $volume, 0.0, 100.0,
        onChange: fn(Player $p, float $v) => saveVolume($p, $v)
    )
    ->spacer()
    ->header('プロフィール')
    ->textField('表示名', $name)
    ->spacer()
    ->button('保存',
        onClick: function(Player $p) use ($name): void {
            saveName($p, $name->getValue());
            $p->sendMessage('保存しました');
        }
    )
    ->closeButton();

$form->show($player);
$form->close($player); // 明示的に閉じる場合
```

---

## Observable（リアクティブ値）

`Observable` は値を保持し、変更時に接続中プレイヤーの UI を自動更新します。

```php
$text = new Observable('初期値');

$text->getValue();         // '初期値'
$text->setValue('新しい値'); // UI が即時反映
```

---

## UI 要素リファレンス

### `button` — ボタン

```php
(new CustomForm('タイトル'))
    ->button('送信',
        onClick: fn(Player $p) => $p->sendMessage('押された'),
        options: new ButtonOptions(tooltip: 'ヒント')
    );
```

**ButtonOptions:**

| プロパティ | 型 | 説明 |
|---|---|---|
| `tooltip` | `string\|Observable` | ツールチップ |
| `disabled` | `bool\|Observable` | 無効化 |
| `visible` | `bool\|Observable` | 表示/非表示 |

---

### `closeButton` — 閉じるボタン

```php
->closeButton(
    onClick: fn(Player $p) => $p->sendMessage('閉じた'),
    options: new CloseButtonOptions(label: '閉じる')
)
```

---

### `toggle` — トグル（オン/オフ）

```php
$enabled = new Observable(false);

->toggle('通知を有効にする', $enabled,
    onChange: fn(Player $p, bool $v) => $p->sendMessage($v ? 'ON' : 'OFF')
)
```

`$enabled->setValue(true)` で UI を即時更新できます。

**ToggleOptions:** `description`, `disabled`, `visible`

---

### `slider` — スライダー

```php
$volume = new Observable(50.0);

->slider('音量', $volume, min: 0.0, max: 100.0,
    onChange: fn(Player $p, float $v) => $p->sendMessage("音量: {$v}"),
    options: new SliderOptions(step: 5)
)
```

**SliderOptions:**

| プロパティ | 型 | 説明 |
|---|---|---|
| `description` | `string\|Observable` | 説明文 |
| `step` | `float\|int\|Observable` | ステップ幅（デフォルト: 1） |
| `disabled` | `bool\|Observable` | 無効化 |
| `visible` | `bool\|Observable` | 表示/非表示 |

---

### `dropdown` — ドロップダウン

```php
$selected = new Observable(0);

->dropdown('難易度', ['Easy', 'Normal', 'Hard'], $selected,
    onChange: fn(Player $p, int $i) => $p->sendMessage("選択: {$i}")
)
```

**DropdownOptions:** `description`, `disabled`, `visible`

---

### `textField` — テキスト入力

```php
$input = new Observable('');

->textField('名前を入力', $input,
    onChange: fn(Player $p, string $v) => $p->sendMessage("入力: {$v}")
)
```

**TextFieldOptions:** `description`, `disabled`, `visible`

---

### `header` / `label` / `spacer` — 表示専用要素

```php
->header('セクション名')
->label('説明テキスト')
->spacer()
```

**HeaderOptions / LabelOptions / SpacerOptions:** `visible`

---

## 要素を動的に更新する

`Observable` に新しい値をセットするだけで、フォームを表示しているすべてのプレイヤーの UI が自動更新されます。

```php
$statusText = new Observable('待機中...');

(new CustomForm('進行状況'))
    ->label('')->setLabelObservable($statusText) // ※要素参照が必要な場合は後述
    ->show($player);

$statusText->setValue('処理中...');
$statusText->setValue('完了!');
```

> **要素参照が必要な場合**（`setVisible` など Observable 以外の操作）は、
> 要素を個別に作成して変数に保持してください。
>
> ```php
> $element = new LabelElement('初期テキスト', null, null);
> $form->layout->setProperty($element); // layout は readonly
> ```
>
> ただし多くの動的変更は `Observable` + Options で対応可能です。

---

## アクティブなフォームの取得

```php
use soradore\DDUI\DataDrivenScreen;

$screen = DataDrivenScreen::getActiveScreen($player);
$screen?->close($player);
```

---

## フォームタイトルをリアクティブに変更する

```php
$title = new Observable('マイフォーム');

(new CustomForm())
    ->setTitleObservable($title)
    ->show($player);

$title->setValue('更新されたタイトル');
```


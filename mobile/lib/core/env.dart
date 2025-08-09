class Env {
  static const bool isAndroidEmu = bool.fromEnvironment('IS_ANDROID_EMU', defaultValue: true);

  // 実機/エミュレータの切替が面倒なので、まずは Androidエミュレータ優先
  static String get baseUrl {
    // Androidエミュレータ → 10.0.2.2
    return 'http://10.0.2.2:8000';
    // iOSシミュレータなら 'http://127.0.0.1:8000'
  }
}

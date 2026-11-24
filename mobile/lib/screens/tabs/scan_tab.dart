import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../../data/dummy_data.dart';
import '../../helper/constant.dart';
import '../../widget/app_widgets.dart';

class ScanTab extends StatefulWidget {
  const ScanTab({this.active = false, super.key});

  final bool active;

  @override
  State<ScanTab> createState() => _ScanTabState();
}

class _ScanTabState extends State<ScanTab> with SingleTickerProviderStateMixin {
  late final AnimationController _scanLine = AnimationController(
    vsync: this,
    duration: const Duration(milliseconds: 2200),
  )..repeat(reverse: true);
  String _scanType = 'Scan Daun';
  final TextEditingController _potNumberController = TextEditingController();
  final FocusNode _potNumberFocusNode = FocusNode();
  bool _showPotError = false;
  bool _flash = false;
  bool _analyzing = false;

  @override
  void dispose() {
    _scanLine.dispose();
    _potNumberController.dispose();
    _potNumberFocusNode.dispose();
    super.dispose();
  }

  Future<void> _analyze() async {
    if (_analyzing) return;
    final potNumber = int.tryParse(_potNumberController.text);
    if (potNumber == null || potNumber <= 0) {
      setState(() => _showPotError = true);
      _potNumberFocusNode.requestFocus();
      return;
    }

    final potId = 'POT-${potNumber.toString().padLeft(2, '0')}';
    FocusScope.of(context).unfocus();
    setState(() => _analyzing = true);
    await Future<void>.delayed(const Duration(seconds: 1));
    if (!mounted) return;
    setState(() => _analyzing = false);
    showAppBottomSheet<void>(
      context: context,
      fullHeight: true,
      builder: (context) => _DiagnosisSheet(
        potId: potId,
        onSave: () {
          Navigator.of(context).pop();
          ScaffoldMessenger.of(this.context).showSnackBar(
            const SnackBar(
              content: Text('Contoh tampilan: belum tersambung ke server'),
            ),
          );
        },
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final keyboardVisible = MediaQuery.viewInsetsOf(context).bottom > 0;
    final polybagBorder = OutlineInputBorder(
      borderRadius: BorderRadius.circular(smallRadius),
      borderSide: BorderSide(
        color: _showPotError ? dangerBorder : Colors.white54,
      ),
    );

    return TickerMode(
      enabled: widget.active,
      child: Stack(
        fit: StackFit.expand,
        children: [
          samplePhoto(url: leafPhoto),
          const DecoratedBox(
            decoration: BoxDecoration(
              gradient: LinearGradient(
                begin: Alignment.topCenter,
                end: Alignment.bottomCenter,
                colors: [
                  Color(0xB8000000),
                  Color(0x18000000),
                  Color(0xD9000000),
                ],
                stops: [0, 0.48, 1],
              ),
            ),
          ),
          Positioned.fill(
            child: SafeArea(
              child: Column(
                children: [
                  Padding(
                    padding: EdgeInsets.fromLTRB(
                      16,
                      keyboardVisible ? 4 : 12,
                      16,
                      0,
                    ),
                    child: Row(
                      children: [
                        const Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'PEMINDAIAN AI',
                                style: TextStyle(
                                  color: Colors.white70,
                                  fontSize: 11,
                                  letterSpacing: 1.6,
                                  fontWeight: FontWeight.w800,
                                ),
                              ),
                              SizedBox(height: 4),
                              Text(
                                'Scan Tanaman',
                                style: TextStyle(
                                  color: Colors.white,
                                  fontSize: 21,
                                  fontWeight: FontWeight.w800,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                  if (!keyboardVisible)
                    Align(
                      alignment: Alignment.centerLeft,
                      child: Padding(
                        padding: const EdgeInsets.only(top: 8, left: 16),
                        child: Text(
                          'Data contoh • kamera dan analisis disimulasikan',
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 11,
                          ),
                        ),
                      ),
                    ),
                  SizedBox(height: keyboardVisible ? space8 : space20),
                  if (!keyboardVisible)
                    Row(
                      mainAxisSize: MainAxisSize.min,
                      children: ['Scan Daun', 'Scan Buah'].map((type) {
                        final selected = type == _scanType;
                        return Padding(
                          padding: const EdgeInsets.symmetric(
                            horizontal: space4,
                          ),
                          child: AppChip(
                            label: type,
                            selected: selected,
                            selectedBackground: Colors.white,
                            selectedForeground: darkGreen,
                            unselectedBackground: const Color(0x33000000),
                            unselectedForeground: Colors.white,
                            unselectedBorder: Colors.white54,
                            onSelected: (_) => setState(() => _scanType = type),
                          ),
                        );
                      }).toList(),
                    ),
                  SizedBox(height: keyboardVisible ? space8 : space16),
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 24),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'ID Polybag',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 11,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                        const SizedBox(height: space4),
                        SizedBox(
                          height: 48,
                          child: TextField(
                            key: const ValueKey('polybag-number'),
                            controller: _potNumberController,
                            focusNode: _potNumberFocusNode,
                            keyboardType: TextInputType.number,
                            textInputAction: TextInputAction.done,
                            textAlignVertical: TextAlignVertical.center,
                            cursorColor: Colors.white,
                            inputFormatters: [
                              FilteringTextInputFormatter.digitsOnly,
                              LengthLimitingTextInputFormatter(3),
                            ],
                            maxLength: 3,
                            onTapOutside: (_) => _potNumberFocusNode.unfocus(),
                            onSubmitted: (_) => _potNumberFocusNode.unfocus(),
                            onChanged: (_) {
                              if (_showPotError) {
                                setState(() => _showPotError = false);
                              }
                            },
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 16,
                              fontWeight: FontWeight.w500,
                            ),
                            decoration: InputDecoration(
                              isDense: true,
                              filled: true,
                              fillColor: Colors.black.withValues(alpha: 0.35),
                              contentPadding: const EdgeInsets.symmetric(
                                horizontal: space12,
                              ),
                              border: polybagBorder,
                              enabledBorder: polybagBorder,
                              focusedBorder: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(
                                  smallRadius,
                                ),
                                borderSide: const BorderSide(
                                  color: Colors.white,
                                  width: 1.5,
                                ),
                              ),
                              counterText: '',
                              prefixText: 'POT-',
                              prefixStyle: const TextStyle(
                                color: Colors.white,
                                fontSize: 16,
                                fontWeight: FontWeight.w700,
                              ),
                              hintText: 'mis. 5',
                              hintStyle: const TextStyle(
                                color: Colors.white60,
                                fontSize: 16,
                                fontWeight: FontWeight.w500,
                              ),
                            ),
                          ),
                        ),
                        if (_showPotError)
                          const Padding(
                            padding: EdgeInsets.only(top: 4),
                            child: Text(
                              'Isi nomor polybag.',
                              key: ValueKey('polybag-number-error'),
                              style: TextStyle(
                                color: dangerBorder,
                                fontSize: 11,
                              ),
                            ),
                          ),
                      ],
                    ),
                  ),
                  Expanded(
                    child: keyboardVisible
                        ? const SizedBox.shrink()
                        : Center(
                            child: SizedBox(
                              width: 250,
                              height: 250,
                              child: AnimatedBuilder(
                                animation: _scanLine,
                                builder: (context, child) => CustomPaint(
                                  painter: _TargetPainter(
                                    linePosition: _scanLine.value,
                                  ),
                                  child: child,
                                ),
                                child: const Center(
                                  child: Text(
                                    'Posisikan daun di dalam bingkai',
                                    textAlign: TextAlign.center,
                                    style: TextStyle(
                                      color: Colors.white,
                                      fontSize: 11,
                                      fontWeight: FontWeight.w600,
                                      shadows: [
                                        Shadow(
                                          color: Colors.black87,
                                          blurRadius: 4,
                                        ),
                                      ],
                                    ),
                                  ),
                                ),
                              ),
                            ),
                          ),
                  ),
                  if (!keyboardVisible) ...[
                    const SizedBox(height: space8),
                    const Text(
                      'Pastikan pencahayaan cukup dan objek terlihat jelas',
                      textAlign: TextAlign.center,
                      style: TextStyle(color: Colors.white, fontSize: 11),
                    ),
                  ],
                  Padding(
                    padding: EdgeInsets.fromLTRB(
                      24,
                      keyboardVisible ? 0 : space8,
                      24,
                      keyboardVisible ? space8 : 24,
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        _ScanAction(
                          icon: _flash ? Icons.flash_on : Icons.flash_off,
                          label: 'Kilat',
                          onTap: () => setState(() => _flash = !_flash),
                        ),
                        InkWell(
                          key: const ValueKey('scan-shutter'),
                          onTap: _analyze,
                          borderRadius: BorderRadius.circular(cardRadius),
                          child: Container(
                            width: 68,
                            height: 68,
                            decoration: const BoxDecoration(
                              shape: BoxShape.circle,
                              color: Colors.white,
                              border: Border.fromBorderSide(
                                BorderSide(color: Colors.white70, width: 4),
                              ),
                            ),
                            child: _analyzing
                                ? const Padding(
                                    padding: EdgeInsets.all(space16),
                                    child: CircularProgressIndicator(
                                      strokeWidth: 2.5,
                                      color: primaryColor,
                                    ),
                                  )
                                : const Icon(
                                    Icons.camera_alt_outlined,
                                    color: darkGreen,
                                    size: 27,
                                  ),
                          ),
                        ),
                        _ScanAction(
                          icon: Icons.photo_library_outlined,
                          label: 'Galeri',
                          onTap: _analyze,
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),
          if (_analyzing)
            Positioned.fill(
              child: ColoredBox(
                color: const Color(0x99000000),
                child: Center(
                  child: Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 18,
                      vertical: 15,
                    ),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(controlRadius),
                    ),
                    child: const Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        SizedBox(
                          width: 18,
                          height: 18,
                          child: CircularProgressIndicator(
                            strokeWidth: 2,
                            color: primaryColor,
                          ),
                        ),
                        SizedBox(width: space8),
                        Text(
                          'Menganalisis tanaman...',
                          style: TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ),
        ],
      ),
    );
  }
}

class _ScanAction extends StatelessWidget {
  const _ScanAction({
    required this.icon,
    required this.label,
    required this.onTap,
  });
  final IconData icon;
  final String label;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      child: SizedBox(
        width: 54,
        height: 48,
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(icon, color: Colors.white, size: 23),
            const SizedBox(height: 4),
            SizedBox(
              height: 14,
              child: FittedBox(
                fit: BoxFit.scaleDown,
                child: Text(
                  label,
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 11,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _TargetPainter extends CustomPainter {
  const _TargetPainter({required this.linePosition});
  final double linePosition;

  @override
  void paint(Canvas canvas, Size size) {
    const length = 28.0;
    const radius = 23.0;
    final paint = Paint()
      ..color = Colors.white
      ..strokeWidth = 3
      ..strokeCap = StrokeCap.round;
    final corners = [
      [Offset(radius, 0), Offset(0, 0), Offset(0, radius)],
      [
        Offset(size.width - radius, 0),
        Offset(size.width, 0),
        Offset(size.width, radius),
      ],
      [
        Offset(0, size.height - radius),
        Offset(0, size.height),
        Offset(radius, size.height),
      ],
      [
        Offset(size.width - radius, size.height),
        Offset(size.width, size.height),
        Offset(size.width, size.height - radius),
      ],
    ];
    for (final corner in corners) {
      canvas.drawLine(corner[0], corner[1], paint);
      canvas.drawLine(corner[1], corner[2], paint);
    }

    final scanPaint = Paint()
      ..color = accentGreen
      ..strokeWidth = 2;
    final y = length + (size.height - length * 2) * linePosition;
    canvas.drawLine(
      Offset(length, y),
      Offset(size.width - length, y),
      scanPaint,
    );
  }

  @override
  bool shouldRepaint(covariant _TargetPainter oldDelegate) =>
      oldDelegate.linePosition != linePosition;
}

class _DiagnosisSheet extends StatelessWidget {
  const _DiagnosisSheet({required this.potId, required this.onSave});
  final String potId;
  final VoidCallback onSave;

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.fromLTRB(20, space8, 20, space24),
      children: [
        Row(
          children: [
            const Expanded(
              child: Text(
                'Hasil Diagnosis AI',
                style: TextStyle(fontSize: 20, fontWeight: FontWeight.w800),
              ),
            ),
            IconButton(
              onPressed: () => Navigator.pop(context),
              icon: const Icon(Icons.close),
            ),
          ],
        ),
        const AppSampleLabel(),
        const SizedBox(height: 12),
        ClipRRect(
          borderRadius: BorderRadius.circular(cardRadius),
          child: Stack(
            children: [
              SizedBox(
                height: 195,
                width: double.infinity,
                child: samplePhoto(url: leafPhoto),
              ),
              Positioned(
                left: 79,
                top: 38,
                width: 105,
                height: 102,
                child: Container(
                  decoration: BoxDecoration(
                    border: Border.all(color: accentGreen, width: 2),
                    borderRadius: BorderRadius.circular(smallRadius),
                  ),
                  child: const Align(
                    alignment: Alignment.topLeft,
                    child: ColoredBox(
                      color: primaryColor,
                      child: Padding(
                        padding: EdgeInsets.symmetric(
                          horizontal: 5,
                          vertical: 3,
                        ),
                        child: Text(
                          '94.8%',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 11,
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                      ),
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: space12),
        const AppBadge(label: 'PERLU TINDAKAN', danger: true),
        const SizedBox(height: 8),
        const Text(
          'Bercak Daun (Leaf Spot)',
          style: TextStyle(fontSize: 19, fontWeight: FontWeight.w800),
        ),
        const SizedBox(height: 4),
        Text(
          'Tingkat akurasi 94.8%  •  $potId',
          style: const TextStyle(color: mutedColor, fontSize: 11),
        ),
        const SizedBox(height: 16),
        AppCard(
          color: paleGreen,
          border: Border.all(color: const Color(0xFFBBF7D0)),
          child: const Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'REKOMENDASI SOP',
                style: TextStyle(
                  color: primaryColor,
                  fontSize: 11,
                  fontWeight: FontWeight.w800,
                  letterSpacing: 1,
                ),
              ),
              SizedBox(height: space8),
              Text(
                'Pangkas daun yang terinfeksi. Pisahkan dari tanaman sehat, lalu pantau kelembapan dan sirkulasi udara selama 48 jam.',
                style: TextStyle(fontSize: 11, height: 1.5),
              ),
            ],
          ),
        ),
        const SizedBox(height: 16),
        AppButton(
          label: 'Simpan & Kirim ke Server',
          icon: Icons.cloud_upload_outlined,
          onPressed: onSave,
        ),
      ],
    );
  }
}

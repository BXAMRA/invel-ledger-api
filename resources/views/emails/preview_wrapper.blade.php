<!DOCTYPE html>
<html>
<head>
<title>Email Preview Side-by-Side</title>
<style>
  body { margin: 0; padding: 0; display: flex; height: 100vh; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #0f172a; overflow: hidden; }
  .pane { height: 100vh; overflow-y: auto; box-sizing: border-box; }
  .html-pane { width: 65%; background: #ffffff; border-right: 1px solid #334155; display: flex; flex-direction: column; }
  .text-pane { width: 35%; background: #1e293b; color: #e2e8f0; padding: 24px; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 13px; line-height: 1.6; white-space: pre-wrap; }
  h2 { margin-top: 0; font-size: 13px; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8; border-bottom: 1px solid #334155; padding-bottom: 12px; margin-bottom: 20px; }
  .html-header { background: #0f172a; padding: 12px 24px; border-bottom: 1px solid #334155; display: flex; align-items: center; justify-content: space-between; }
  .html-header h2 { margin: 0; border: none; padding: 0; }
</style>
</head>
<body>
  <div class="pane html-pane">
    <div class="html-header">
      <h2>HTML Version</h2>
    </div>
    <iframe srcdoc="{{ $html }}" style="flex: 1; width: 100%; border: none; background: #f8fafc;"></iframe>
  </div>
  <div class="pane text-pane">
    <h2>Plain Text Version</h2>
    {!! nl2br(e($text)) !!}
  </div>
</body>
</html>

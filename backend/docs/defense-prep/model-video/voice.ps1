# Speaks voice/captions.json with an offline Windows voice into voice/c00.wav ... and writes voice/durations.json (ms).
param([string]$Voice = 'Microsoft Zira Desktop', [int]$Rate = 0)
Add-Type -AssemblyName System.Speech
$dir = Join-Path $PSScriptRoot 'voice'
$caps = Get-Content (Join-Path $dir 'captions.json') -Raw -Encoding UTF8 | ConvertFrom-Json
$fmt = New-Object System.Speech.AudioFormat.SpeechAudioFormatInfo(22050, [System.Speech.AudioFormat.AudioBitsPerSample]::Sixteen, [System.Speech.AudioFormat.AudioChannel]::Mono)
$durs = @()
$i = 0
foreach ($c in $caps) {
  $f = Join-Path $dir ('c{0:D2}.wav' -f $i)
  $s = New-Object System.Speech.Synthesis.SpeechSynthesizer
  $s.SelectVoice($Voice); $s.Rate = $Rate
  $s.SetOutputToWaveFile($f, $fmt); $s.Speak($c.s); $s.Dispose()
  $bytes = (Get-Item $f).Length - 44
  $durs += [math]::Round($bytes / (22050 * 2) * 1000)
  $i++
}
($durs | ConvertTo-Json -Compress) | Set-Content (Join-Path $dir 'durations.json') -Encoding ASCII
"clips $i, total $([math]::Round(($durs | Measure-Object -Sum).Sum / 1000)) s"

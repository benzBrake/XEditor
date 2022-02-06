$StartDir = ($pwd).Path
$workDir = Split-Path -parent $StartDir
$workParent = Split-Path -parent $workDir

$indexFile = -Join($workDir, '\Plugin.php')
$excludeFile = -Join($workDir, '\tools\pack.exclude')
$zipexe = "C:\Tools\7z\7z.exe"
$TRUE_FALSE=(Test-Path $indexFile)
if ($TRUE_FALSE -ne "True") {
	$indexFile -Join($workDir, '\Plugin.php')
	$TRUE_FALSE=(Test-Path $indexFile)
	if ($TRUE_FALSE -ne "True") {
		Write-Host Do Nothing
		Exit
	}
}
$string = Get-Content $indexFile | Select-String -Pattern "@package" -SimpleMatch | select-object -First 1
$package = $string.line.split(" ")[3]
$string = Get-Content $indexFile | Select-String -Pattern "@version" -SimpleMatch | select-object -First 1
$version = $string.line.split(" ")[3]
$stamp = Get-Date -Format 'yyyyMMdd'
$archiveName = "$($package)-$($version)-$($stamp).zip"
$excludeList = @(Get-Content -Path $excludeFile)
$tempExludeFile = "$workDir\tools\pack.exclude.tmp"
Remove-Item  -ErrorAction Ignore "$workDir\pack\$archiveName"
Remove-Item  -ErrorAction Ignore $tempExludeFile
for($i=0;$i -lt $excludeList.Length;) {
	-Join("$package\", $excludeList[$i]) | Add-Content -Path $tempExludeFile
	$i++
}
cd $workParent
& $zipexe a -tzip -r -x"@$tempExludeFile" -spf "$workDir\pack\$archiveName" "$package"
Remove-Item $tempExludeFile
cd $StartDir

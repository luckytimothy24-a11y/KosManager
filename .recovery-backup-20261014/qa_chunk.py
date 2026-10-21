import subprocess, pathlib, sys
lst = [l.strip().lstrip('\ufeff') for l in open('.recovery-backup-20261014/feature_list.txt',encoding='utf-8-sig') if l.strip().endswith('.php')]
# chunk by 20 files
chunk_size=20
out=[]
php=r'C:\xampp\php\php.exe'
if not pathlib.Path(php).exists():
    php=sys.executable
for i in range(0,len(lst),chunk_size):
    chunk=lst[i:i+chunk_size]
    cmd=[php, 'vendor/bin/phpunit']+chunk
    print(f"=== CHUNK {i//chunk_size+1}: {len(chunk)} files ({chunk[0]} .. {chunk[-1]}) ===")
    res=subprocess.run(cmd, capture_output=True, text=True, timeout=300, encoding='utf-8', errors='replace')
    txt=(res.stdout or '') + (res.stderr or '')
    # extract summary line
    for line in txt.splitlines():
        if 'Tests:' in line or 'OK' in line or 'FAILURES' in line or 'ERRORS' in line:
            print(line.strip())
    if res.returncode!=0:
        # print failures
        snippet=[l for l in txt.splitlines() if 'FAIL' in l or 'ERROR' in l or 'Tests\\' in l][:30]
        for l in snippet:
            print(l)
        # save full
        open(f'.recovery-backup-20261014/chunk_{i//chunk_size+1}_out.txt','w',encoding='utf-8').write(txt)
    out.append(res.returncode)
print("DONE codes",out)

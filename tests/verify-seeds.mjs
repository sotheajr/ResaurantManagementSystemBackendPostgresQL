const BASE = 'http://localhost:8000/api';

(async () => {
  const loginRes = await fetch(BASE + '/login', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ username: 'admin', password: 'admin123' }) });
  const loginJson = await loginRes.json();
  console.log('LOGIN STATUS:', loginRes.status);
  if (!loginJson.data?.token) {
    console.log('LOGIN BODY:', JSON.stringify(loginJson).slice(0, 400));
    process.exit(1);
  }
  const token = loginJson.data.token;
  const h = { Authorization: `Bearer ${token}` };

  const endpoints = [
    'categories', 'menu', 'tables', 'customers', 'reservations',
    'suppliers', 'inventory', 'partners', 'attendance', 'payroll',
    'sub-categories',
  ];

  for (const ep of endpoints) {
    const res = await fetch(`${BASE}/${ep}`, { headers: h });
    const json = await res.json();
    const count = Array.isArray(json.data) ? json.data.length : (json.data ? 1 : 0);
    console.log(`${ep.padEnd(15)} => ${res.status}  ${count} record(s)`);
  }
})().catch(e => { console.error('FATAL:', e.message); process.exit(1); });
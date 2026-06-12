import { Routes, Route } from "react-router-dom";
import MainLayout from "./layout/MainLayout";
import Dashboard from "./pages/Dashboard";

export default function App() {
  return (
    <Routes>
      <Route path="/" element={<MainLayout />}>
        <Route index element={<Dashboard />} />
        <Route path="users" element={<h2>Users</h2>} />
        <Route path="reports" element={<h2>Reports</h2>} />
        <Route path="reports/redirect" element={<h2>Redirect Reports</h2>} />
        <Route path="reports/automation" element={<h2>Automation</h2>} />
      </Route>
    </Routes>
  );
}
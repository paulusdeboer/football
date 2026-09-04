import { useEffect, useRef } from 'react';
import Chart from 'chart.js/auto';

export default function ChartCard({ id, type, labels, values, label }) {
    const canvas = useRef(null);

    useEffect(() => {
        if (!canvas.current) return undefined;
        const chart = new Chart(canvas.current, {
            type,
            data: { labels, datasets: [{ label, data: values }] },
            options: { responsive: true, maintainAspectRatio: false },
        });
        return () => chart.destroy();
    }, [type, labels, values, label]);

    return <div className="card mb-4"><div className="card-body chart-container"><canvas id={id} ref={canvas} /></div></div>;
}

// 公告每天只弹出一次
document.addEventListener('DOMContentLoaded', function() {
    // 获取当前日期（格式：YYYY-MM-DD）
    const today = new Date().toISOString().split('T')[0];
    const shownNoticeDate = localStorage.getItem('shownNoticeDate');
    const noticeContainer = document.getElementById('notice-modal');

    // 若今日未弹出过公告，请求公告数据并显示
    if (today !== shownNoticeDate && noticeContainer) {
        fetch('../actions/get_notice.php')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success' && data.notice) {
                    // 填充公告内容
                    document.getElementById('notice-title').textContent = data.notice.title;
                    document.getElementById('notice-content').textContent = data.notice.content;
                    // 显示公告弹窗
                    noticeContainer.style.display = 'block';
                    // 记录今日已弹出
                    localStorage.setItem('shownNoticeDate', today);
                }
            })
            .catch(error => console.error('获取公告失败：', error));
    }

    // 关闭公告弹窗
    document.getElementById('close-notice').addEventListener('click', function() {
        document.getElementById('notice-modal').style.display = 'none';
    });
});
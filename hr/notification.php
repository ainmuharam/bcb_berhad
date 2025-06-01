<?php
class Notification {
    private $request;
    private $emp_id;

    public function __construct(Request $request, $emp_id) {
        $this->request = $request;
        $this->emp_id = $emp_id;
    }

    public function getCurrentMonthRequests() {
        $results = $this->request->getUserPendingRequest($this->emp_id);

        $currentMonth = date('m');
        $currentYear = date('Y');
        $filtered = [];

        while ($row = $results->fetch_assoc()) {
            $rowMonth = date('m', strtotime($row['date']));
            $rowYear = date('Y', strtotime($row['date']));

            if ($rowMonth === $currentMonth && $rowYear === $currentYear) {
                $filtered[] = $row;
            }
        }

        return $filtered;
    }

    public function getUnreadCount() {
        $current = $this->getCurrentMonthRequests();
        return count($current);
    }

    public function renderNotifications() {
        $requests = $this->getCurrentMonthRequests();

        if (empty($requests)) {
            return "<p>No new notifications for " . date('F Y') . ".</p>";
        }

        $output = '';
        foreach ($requests as $row) {
            $date = date("d M Y", strtotime($row['date']));
            $status = $row['status'] === 'none' ? 'pending' : $row['status'];
            $message = match ($status) {
                'pending' => "Your request is <strong>pending</strong>. Please notify HR.",
                'Approved' => "Your request has been <strong>approved</strong>.",
                'Rejected' => "Your request has been <strong>rejected</strong>.",
                default => "Status unknown."
            };

            $output .= "<p><strong>$date</strong>: $message</p>";
        }

        return $output;
    }
}

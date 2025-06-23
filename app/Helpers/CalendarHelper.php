<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class CalendarHelper
{
    /**
     * 指定月のカレンダーデータを生成
     *
     * @param Carbon|null $targetDate 対象月（nullの場合は今月）
     * @return array
     */
    public static function generateMonthCalendar(?Carbon $targetDate = null): array
    {
        $targetDate = $targetDate ?? Carbon::now();
        
        // 今月の1日を取得
        $currentDate = $targetDate->copy()->startOfMonth();
        $endDate = $currentDate->copy()->endOfMonth();
        
        // 月の最初の週の日曜日を取得（日本式カレンダーのため）
        $startDate = $currentDate->copy();
        while ($startDate->dayOfWeek !== 0) { // 0 = 日曜日
            $startDate->subDay();
        }
        
        // 月の最後の週の土曜日を取得
        $calendarEndDate = $endDate->copy();
        while ($calendarEndDate->dayOfWeek !== 6) { // 6 = 土曜日
            $calendarEndDate->addDay();
        }
        
        // 全日付を配列として生成
        $calendarDates = [];
        $tempDate = $startDate->copy();
        while ($tempDate->lte($calendarEndDate)) {
            $calendarDates[] = $tempDate->copy();
            $tempDate->addDay();
        }
        
        // 週ごとに分割
        $weeks = array_chunk($calendarDates, 7);
        
        return [
            'weeks' => $weeks,
            'currentDate' => $currentDate,
            'targetYear' => $targetDate->year,
            'targetMonth' => $targetDate->month,
            'displayMonth' => $targetDate->format('Y年m月')
        ];
    }
    
    /**
     * 日付に対応するシフトをフィルタリング
     *
     * @param Collection $shifts
     * @param Carbon $date
     * @return Collection
     */
    public static function getShiftsForDate(Collection $shifts, Carbon $date): Collection
    {
        return $shifts->filter(function($shift) use ($date) {
            return Carbon::parse($shift->start_time)->format('Y-m-d') === $date->format('Y-m-d');
        });
    }
    
    /**
     * 日付のスタイルクラスを取得
     *
     * @param Carbon $date
     * @param Carbon $currentDate
     * @return string
     */
    public static function getDateStyleClass(Carbon $date, Carbon $currentDate): string
    {
        $isCurrentMonth = $date->month === $currentDate->month;
        $isToday = $date->isToday();
        
        $classes = ['border', 'border-gray-200', 'h-20', 'p-1'];
        
        if ($isCurrentMonth) {
            $classes[] = 'bg-white';
        } else {
            $classes[] = 'bg-gray-100';
        }
        
        if ($isToday) {
            $classes[] = 'bg-blue-50';
            $classes[] = 'border-blue-300';
        }
        
        return implode(' ', $classes);
    }
    
    /**
     * 日付テキストのスタイルクラスを取得
     *
     * @param Carbon $date
     * @param Carbon $currentDate
     * @return string
     */
    public static function getDateTextStyleClass(Carbon $date, Carbon $currentDate): string
    {
        $isCurrentMonth = $date->month === $currentDate->month;
        $isToday = $date->isToday();
        
        $classes = ['text-xs'];
        
        if ($isCurrentMonth) {
            $classes[] = 'text-gray-900';
        } else {
            $classes[] = 'text-gray-400';
        }
        
        if ($isToday) {
            $classes[] = 'font-bold';
            $classes[] = 'text-blue-600';
        }
        
        return implode(' ', $classes);
    }
    
    /**
     * 曜日ヘッダーを取得
     *
     * @return array
     */
    public static function getDayHeaders(): array
    {
        return ['日', '月', '火', '水', '木', '金', '土'];
    }
}
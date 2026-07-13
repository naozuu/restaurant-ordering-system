<?php

function orderStatusLabel(string $status): string
{
    return [
        'menunggu' => 'Waiting',
        'diproses' => 'Processing',
        'siap' => 'Ready',
        'selesai' => 'Completed',
        'dibatalkan' => 'Cancelled',
    ][$status] ?? ucwords(str_replace('_', ' ', $status));
}

function paymentStatusLabel(string $status): string
{
    return [
        'belum_bayar' => 'Unpaid',
        'sudah_bayar' => 'Paid',
    ][$status] ?? ucwords(str_replace('_', ' ', $status));
}

function menuStatusLabel(string $status): string
{
    return [
        'tersedia' => 'Available',
        'habis' => 'Sold Out',
    ][$status] ?? ucfirst($status);
}

function categoryLabel(string $category): string
{
    return [
        'Makanan' => 'Food',
        'Minuman' => 'Drinks',
        'Dessert' => 'Dessert',
    ][$category] ?? $category;
}

function roleLabel(string $role): string
{
    return [
        'admin' => 'Administrator',
        'kasir' => 'Cashier',
    ][$role] ?? ucfirst($role);
}
